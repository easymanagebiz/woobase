<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Easymanage_Mailtemplate {

  const SHORTOCODE_REGEXP = "/(?P<shortcode>(?:(?:\\s?\\[))(?P<name>[\\w\\-]{3,})(?:\\s(?P<attrs>[\\w\\d,\\s=\\\"\\'\\-\\+\\#\\%\\!\\~\\`\\&\\.\\s\\:\\/\\?\\|]+))?(?:\\])(?:(?P<content>[\\w\\d\\,\\!\\@\\#\\$\\%\\^\\&\\*\\(\\\\)\\s\\=\\\"\\'\\-\\+\\&\\.\\s\\:\\/\\?\\|\\<\\>]+)(?:\\[\\/[\\w\\-\\_]+\\]))?)/u";

  const ATTRIBUTE_REGEXP = "/(?<name>\\S+)=[\"']?(?P<value>(?:.(?![\"']?\\s+(?:\\S+)=|[>\"']))+.)[\"']?/u";

	const DEFAULT_PRODUCT_LIMIT = 4;

	protected $_content;

	protected $_contentFinal;

	protected $_subject;

	protected $_baseTemplate;

	protected $_shortcodesDefault = [
		'product',
    'category'
	];

	public function processTemplate($templateObject) {
		$this->_content = $templateObject->email_content;
		$this->_subject = $templateObject->email_subject;

		$this->_shortcodes = $this->parseShortcodes($this->_content);
		$newContent = $this->_content;
		foreach($this->_shortcodes as $code) {
      if(!in_array($code['name'], $this->_shortcodesDefault)) {
        continue;
      }
      $newContent = $this->_processShortcode($newContent, $code);
    }
		$addonInstance = Easymanage_Addon::getInstance();
		$addonInstance->setEmailContent($newContent);
		$addonInstance->setEmailShortCodes($this->_shortcodes);

		do_action( 'easymanage_addons_email_shortcodes', $addonInstance );

		$this->_contentFinal = $addonInstance->getEmailContent();


		$this->prepareBaseTemplate();
	}

	public function getShortCodes() {
		return $this->_shortcodes;
	}

	public function getContent() {
		return $this->_content;
	}

	public function getContentFinal() {
		return $this->_contentFinal;
	}

	public function getSubject() {
		return $this->_subject;
	}

	public function getBaseTemplate() {
		return $this->_baseTemplate;
	}

	protected function prepareBaseTemplate() {
		$this->_baseTemplate  = '';
		$this->_baseTemplate .= wc_get_template_html( 'mailtemplates/head.php', [], '', EASYMANAGE_TEMPLATE_PATH );
		$this->_baseTemplate .= '[[mail_content]]';
		$this->_baseTemplate .= wc_get_template_html( 'mailtemplates/footer.php', [], '', EASYMANAGE_TEMPLATE_PATH );
	}

	protected function _processShortcode($newContent, $code) {

    switch($code['name']) {

      case 'product':
        if(empty($code['attrs'][0])) {
          return $newContent;
        }
				$attrs = $code['attrs'][0];
				if(empty($attrs['product_sku'])) {
					$product = $this->load_product($attrs['product_id']);
				}else{
					$product = $this->load_product(null, $attrs['product_sku']);
				}

				if($product) {
					$template_code = wc_get_template_html( 'mailtemplates/product.php', [
						'product' => $product
					], '', EASYMANAGE_TEMPLATE_PATH );
				}else{
					$template_code = '<p>product not found! ' . $code['shortcode'] . ' </p>';
				}

        $newContent = str_replace($code['shortcode'], $template_code, $newContent);

      break;
      case 'category':
				if(empty($code['attrs'][0])) {
					return $newContent;
				}
				$attrs = $code['attrs'][0];
				if(empty( $attrs['category_id'] )) {
					return $newContent;
				}
				$limit = !empty( $attrs['limit'] ) ? $attrs['limit'] : self::DEFAULT_PRODUCT_LIMIT;
				$id = $attrs['category_id'];
				$products = $this->get_category_products( $id, $limit );
				if(!$products) {
					return $newContent;
				}
				$content = '';
				foreach($products as $_product) {
					$content .= wc_get_template_html( 'mailtemplates/product.php', [
						'product' => $_product
					], '', EASYMANAGE_TEMPLATE_PATH );
				}

        $newContent = str_replace($code['shortcode'], $content, $newContent);
			break;

    }

    return $newContent;
  }

	protected function get_category_products( $id, $limit ) {
		$slug = $this->get_category_slug( $id );
		if(!$slug) {
			return;
		}
		$args = array(
    	'category' => array( $slug ),
			'limit' => $limit
		);
		$products = wc_get_products( $args );
		return $products;
	}

	protected function get_category_slug( $id ){
    $term = get_term_by('id', $id, 'product_cat', 'ARRAY_A');
    return !empty($term['slug']) ? $term['slug'] : null;
	}

	protected function load_product($id = null, $sku = null) {

		$qurey = new WC_Product_Query();

		if($sku) {
			$args = [
				'sku' => $sku
			];
			$products = wc_get_products( $args );
		}

		if($id && !$sku) {
			$args = [
				'include' => [$id]
			];
			$products = wc_get_products( $args );
		}

		if(!empty( $products )) {
			return $products[0];
		}
	}

  protected function parseShortcodes($text) {
    preg_match_all(self::SHORTOCODE_REGEXP, $text, $matches, PREG_SET_ORDER);
    $shortcodes = array();
    foreach ($matches as $i => $value) {
        $shortcodes[$i]['shortcode'] = $value['shortcode'];
        $shortcodes[$i]['name'] = $value['name'];
        if (isset($value['attrs'])) {
            $attrs = $this->parse_attrs($value['attrs']);
            $shortcodes[$i]['attrs'] = $attrs;
        }
        if (isset($value['content'])) {
            $shortcodes[$i]['content'] = $value['content'];
        }
    }

    return $shortcodes;
  }

	private function parse_attrs($attrs) {
      preg_match_all(self::ATTRIBUTE_REGEXP, $attrs, $matches, PREG_SET_ORDER);
      $attributes = array();
      foreach ($matches as $i => $value) {
          $key = $value['name'];
          $attributes[$i][$key] = str_replace('"', '', $value['value']);
      }
      return $attributes;
  }

}
