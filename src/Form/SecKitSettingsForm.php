<?php

/**
 * @file
 * Contains \Drupal\seckit\Form\SecKitSettingsForm.
 */

namespace Drupal\seckit\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Implements a form to collect security check configuration.
 */
class SecKitSettingsForm extends ConfigFormBase {
  /**
   * {@inheritdoc}.
   */
  public function getFormId() {
    return 'seckit_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['seckit.settings'];
  }

  /**
   * {@inheritdoc}.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $module_path = drupal_get_path('module', 'seckit');
    $form['#attached']['library'][] = 'seckit/listener';

    $config = \Drupal::config('seckit.settings');
    // main description
    $args['!browserscope'] =  t('<a href="@url">Browserscope</a>', array('@url' => Url::fromUri('http://www.browserscope.org/?category=security')));
    $form['seckit_description'] = array(
      '#type' => 'item',
      '#description' => t('This module provides your website with various options to mitigate risks of common web application vulnerabilities like Cross-site Scripting, Cross-site Request Forgery and Clickjacking. It also has some options to improve your SSL/TLS security and fixes Drupal 6 core Upload module issue leading to an easy exploitation of an old Internet Explorer MIME sniffer HTML injection vulnerability. Note that some security features are not supported by all browsers. You may find this out at !browserscope.', $args),
    );

    // main fieldset for XSS
    $form['seckit_xss'] = array(
      '#type' => 'details',
      '#title' => t('Cross-site Scripting'),
      '#collapsible' => TRUE,
      '#tree' => TRUE,
      '#open' => TRUE,
      '#description' => t('Configure levels and various techniques of protection from cross-site scripting attacks'),
    );

    // fieldset for Content Security Policy (CSP)

    $args['!wiki'] = t('<a href="@url">Mozilla Wiki</a>', array('@url' => Url::fromUri('https://wiki.mozilla.org/Security/CSP')));
    $description = t('Content Security Policy is a policy framework that allows to specify trustworthy sources of content and to restrict its capabilities. You may read more about it at !wiki.', $args);
    $form['seckit_xss']['csp'] = array(
      '#type' => 'details',
      '#title' => t('Content Security Policy'),
      '#collapsible' => TRUE,
      '#tree' => TRUE,
      '#open' => TRUE,
      '#description' => $description,
    );
    // CSP enable/disable
    $form['seckit_xss']['csp']['checkbox'] = array(
      '#type' => 'checkbox',
      '#default_value' => $config->get('seckit_xss.csp.checkbox'),
      '#title' => t('Send HTTP response header'),
      '#return_value' => 1,
      '#description' => t('Send Content-Security-Policy (official), X-Content-Security-Policy (supported by Mozilla Firefox and IE10) and X-WebKit-CSP (supported by Google Chrome and Safari) HTTP response headers with the list of Content Security Policy directives.'),
    );
    // CSP report-only mode
    $form['seckit_xss']['csp']['report-only'] = array(
      '#type' => 'checkbox',
      '#default_value' => $config->get('seckit_xss.csp.report-only'),
      '#title' => t('Report Only'),
      '#return_value' => 1,
      '#description' => t('Use Content Security Policy in report-only mode. In this case, violations of policies will only be reported, not blocked. Use this while configuring policies. Reports are logged to watchdog.'),
    );
    // CSP description
    $items = array(
      "'none' - block content from any source",
      "'self' - allow content only from your domain",
      "'unsafe-inline' - allow specific inline content (note, that it is supported by a subset of directives)",
      "'unsafe-eval' - allow a set of string-to-code API which is restricted by default (supported by script-src directive)"
    );
    $args['!keywords'] = $this->_getItemsList($items);
    $items = array('* - load content from any source', '*.example.com - load content from example.com and all its subdomains', 'example.com:* - load content from example.com via any port.  Otherwise, it will use your website default port');
    $args['!wildcards'] =  $this->_getItemsList($items);
    $args['!spec'] = t('<a href="@url">specification page</a>', array('@url' => Url::fromUri('http://www.w3.org/TR/CSP/')));
    $description = t("Set up security policy for different types of content. Don't use www prefix. Keywords are: !keywords Wildcards (*) are allowed: !wildcards More information is available at !spec.", $args);
    $form['seckit_xss']['csp']['description'] = array(
      '#type' => 'item',
      '#title' => t('Directives'),
      '#description' => $description,
    );
    // CSP default-src directive
    $form['seckit_xss']['csp']['default-src'] = array(
      '#type' => 'textfield',
      '#maxlength'=> 1024,
      '#default_value' => $config->get('seckit_xss.csp.default-src'),
      '#title' => 'default-src',
      '#description' => t("Specify security policy for all types of content, which are not specified further (frame-ancestors excepted). Default is 'self'."),
    );
    // CSP script-src directive
    $form['seckit_xss']['csp']['script-src'] = array(
      '#type' => 'textfield',
      '#maxlength'=> 1024,
      '#default_value' => $config->get('seckit_xss.csp.script-src'),
      '#title' => 'script-src',
      '#description' => t('Specify trustworthy sources for &lt;script&gt; elements.'),
    );
    // CSP object-src directive
    $form['seckit_xss']['csp']['object-src'] = array(
      '#type' => 'textfield',
      '#maxlength'=> 1024,
      '#default_value' => $config->get('seckit_xss.csp.object-src'),
      '#title' => 'object-src',
      '#description' => t('Specify trustworthy sources for &lt;object&gt;, &lt;embed&gt; and &lt;applet&gt; elements.'),
    );
    // CSP style-src directive
    $form['seckit_xss']['csp']['style-src'] = array(
      '#type' => 'textfield',
      '#maxlength'=> 1024,
      '#default_value' => $config->get('seckit_xss.csp.style-src'),
      '#title' => 'style-src',
      '#description' => t('Specify trustworthy sources for stylesheets. Note, that inline stylesheets and style attributes of HTML elements are allowed.'),
    );
    // CSP img-src directive
    $form['seckit_xss']['csp']['img-src'] = array(
      '#type' => 'textfield',
      '#maxlength'=> 1024,
      '#default_value' => $config->get('seckit_xss.csp.img-src'),
      '#title' => 'img-src',
      '#description' => t('Specify trustworthy sources for &lt;img&gt; elements.'),
    );
    // CSP media-src directive
    $form['seckit_xss']['csp']['media-src'] = array(
      '#type' => 'textfield',
      '#maxlength'=> 1024,
      '#default_value' => $config->get('seckit_xss.csp.media-src'),
      '#title' => 'media-src',
      '#description' => t('Specify trustworthy sources for &lt;audio&gt; and &lt;video&gt; elements.'),
    );
    // CSP frame-src directive
    $form['seckit_xss']['csp']['frame-src'] = array(
      '#type' => 'textfield',
      '#maxlength'=> 1024,
      '#default_value' => $config->get('seckit_xss.csp.frame-src'),
      '#title' => 'frame-src',
      '#description' => t('Specify trustworthy sources for &lt;iframe&gt; and &lt;frame&gt; elements.'),
    );
    // CSP font-src directive
    $form['seckit_xss']['csp']['font-src'] = array(
      '#type' => 'textfield',
      '#maxlength'=> 1024,
      '#default_value' => $config->get('seckit_xss.csp.font-src'),
      '#title' => 'font-src',
      '#description' => t('Specify trustworthy sources for @font-src CSS loads.'),
    );
    // CSP connect-src directive
    $form['seckit_xss']['csp']['connect-src'] = array(
      '#type' => 'textfield',
      '#maxlength'=> 1024,
      '#default_value' => $config->get('seckit_xss.csp.connect-src'),
      '#title' => 'connect-src',
      '#description' => t('Specify trustworthy sources for XMLHttpRequest, WebSocket and EventSource connections.'),
    );
    // CSP report-uri directive
    $form['seckit_xss']['csp']['report-uri'] = array(
      '#type' => 'textfield',
      '#maxlength'=> 1024,
      '#default_value' =>  $config->get('seckit_xss.csp.report-uri'),
      '#title' => 'report-uri',
      '#description' => t('Specify a URL (relative to the Drupal root) to which user-agents will report CSP violations. Use the default value, unless you have set up an alternative handler for these reports. Defaults to <code>admin/config/system/seckit/csp-report</code> which logs the report data in watchdog.'),
    );
    // CSP policy-uri directive
    $form['seckit_xss']['csp']['policy-uri'] = array(
      '#type' => 'textfield',
      '#maxlength'=> 1024,
      '#default_value' => $config->get('seckit_xss.csp.policy-uri'),
      '#title' => 'policy-uri',
      '#description' => t("Specify a URL (relative to the Drupal root) for a file containing the (entire) policy. <strong>All other directives will be omitted</strong> by Security Kit, as <code>policy-uri</code> may only be defined in the <em>absence</em> of other policy definitions in the <code>X-Content-Security-Policy</code> HTTP header. The MIME type for this URI <strong>must</strong> be <code>text/x-content-security-policy</code>, otherwise user-agents will enforce the policy <code>allow 'none'</code>  instead."),
    );

    // fieldset for X-XSS-Protection
    $form['seckit_xss']['x_xss'] = array(
      '#type' => 'details',
      '#title' => t('X-XSS-Protection'),
      '#collapsible' => TRUE,
      '#tree' => TRUE,
      '#open' => TRUE,
      '#description' => t('X-XSS-Protection HTTP response header controls Microsoft Internet Explorer, Google Chrome and Apple Safari internal XSS filters.'),
    );
    // options for X-XSS-Protection
    $x_xss_protection_options = array(
      SECKIT_X_XSS_DISABLE => $config->get('seckit_xss.x_xss.seckit_x_xss_option_disable', t('Disabled')),
      SECKIT_X_XSS_0 => $config->get('seckit_xss.x_xss.seckit_x_xss_option_0', '0'),
      SECKIT_X_XSS_1 => $config->get('seckit_xss.x_xss.seckit_x_xss_option_1', '1; mode=block'),
    );
    // configure X-XSS-Protection
    $link = t('<a href="@url">IE\'s XSS filter security flaws in past</a>', array('@url' => Url::fromUri('http://hackademix.net/2009/11/21/ies-xss-filter-creates-xss-vulnerabilities')));
    $items = array('Disabled - XSS filter will work in default mode. Enabled by default', '0 - XSS filter will be disabled for a website. It may be useful because of ' . $link, '1; mode=block - XSS filter will be left enabled, but it will block entire page instead of modifying dangerous content');
    $args['!values'] =  $this->_getItemsList($items);
    $form['seckit_xss']['x_xss']['select'] = array(
      '#type' => 'select',
      '#title' => t('Configure'),
      '#options' => $x_xss_protection_options,
      '#default_value' => $config->get('seckit_xss.x_xss.select'),
      '#description' => t('!values', $args),
    );

    // fieldset for X-Content-Type-Options
    $args['!link'] = t('<a href="@url">MSDN article</a>', array('@url' => Url::fromUri('http://blogs.msdn.com/b/ie/archive/2010/10/26/mime-handling-changes-in-internet-explorer.aspx')));
    $form['seckit_xss']['x_content_type'] = array(
      '#type' => 'details',
      '#title' => t('X-Content-Type-Options'),
      '#collapsible' => TRUE,
      '#tree' => TRUE,
      '#open' => TRUE,
      '#description' => t('X-Content-Type-Options HTTP response header prevents browser from upsniffing content and serving files with inappropriate MIME type. More information is available at !link.', $args),
    );
    // enable/disable X-Content-Type-Options
    $form['seckit_xss']['x_content_type']['checkbox'] = array(
      '#type' => 'checkbox',
      '#title' => t('Send HTTP response header'),
      '#default_value' => $config->get('seckit_xss.x_content_type.checkbox'),
      '#description' => t('Enable X-Content-Type-Options: nosniff HTTP response header.'),
    );

    // main fieldset for CSRF
    $form['seckit_csrf'] = array(
      '#type' => 'details',
      '#title' => t('Cross-site Request Forgery'),
      '#tree' => TRUE,
      '#open' => TRUE,
      '#collapsible' => TRUE,
      '#description' => t('Configure levels and various techniques of protection from cross-site request forgery attacks'),
    );

    // enable/disable Origin
    $form['seckit_csrf']['origin'] = array(
      '#type' => 'checkbox',
      '#title' => t('HTTP Origin'),
      '#default_value' => $config->get('seckit_csrf.origin'),
      '#description' => t('Check Origin HTTP request header.'),
    );
    // Origin whitelist
    $description = t('Comma separated list of trustworthy sources. Do not enter your website URL - it is automatically added. Syntax of the source is: [protocol] :// [host] : [port] . E.g, http://example.com, https://example.com, https://www.example.com, http://www.example.com:8080');
    $form['seckit_csrf']['origin_whitelist'] = array(
      '#type' => 'textfield',
      '#title' => t('Allow requests from'),
      '#default_value' => $config->get('seckit_csrf.origin_whitelist'),
      '#size' => 90,
      '#description' => $description,
    );

    // main fieldset for Clickjacking
    $form['seckit_clickjacking'] = array(
      '#type' => 'details',
      '#title' => t('Clickjacking'),
      '#collapsible' => TRUE,
      '#tree' => TRUE,
      '#open' => TRUE,
      '#description' => t('Configure levels and various techniques of protection from Clickjacking/UI Redressing attacks'),
    );

    // options for X-Frame-Options
    $x_frame_options = array(
      SECKIT_X_FRAME_DISABLE => t('Disabled'),
      SECKIT_X_FRAME_SAMEORIGIN => 'SameOrigin',
      SECKIT_X_FRAME_DENY => 'Deny',
      SECKIT_X_FRAME_ALLOW_FROM => 'Allow-From',
    );
    // configure X-Frame-Options
    $items = array('Disabled - turn off X-Frame-Options', 'SameOrigin - browser allows all the attempts of framing website within its domain. Enabled by default', 'Deny - browser rejects any attempt of framing website', 'Allow-From - browser allows framing website only from specified source');
    $args['!values'] = $this->_getItemsList($items);
    $args['!msdn'] =  t('<a href="@url">MSDN article</a>', array('@url' => Url::fromUri('http://blogs.msdn.com/b/ie/archive/2009/01/27/ie8-security-part-vii-clickjacking-defenses.aspx')));
    $args['!spec'] = t('<a href="@url">specification</a>', array('@url' => Url::fromUri('http://tools.ietf.org/html/draft-ietf-websec-x-frame-options-01')));
    $description = t("X-Frame-Options HTTP response header controls browser's policy of frame rendering. Possible values: !values You may read more about it at !msdn or !spec.", $args);
    $form['seckit_clickjacking']['x_frame'] = array(
      '#type' => 'select',
      '#title' => t('X-Frame-Options'),
      '#options' => $x_frame_options,
      '#default_value' => $config->get('seckit_clickjacking.x_frame'),
      '#description' => $description,
    );

    // Origin value for "Allow-From" option.
    $form['seckit_clickjacking']['x_frame_allow_from'] = array(
      '#type' => 'textarea',
      '#title' => t('Allow-From'),
      '#default_value' => $config->get('seckit_clickjacking.x_frame_allow_from'),
      '#description' => t('Origin URIs (as specified by RFC 6454) for the "X-Frame-Options: Allow-From" value. One per line. Example, http://domain.com'),
    );

    // enable/disable JS + CSS + Noscript protection
    $args['!link'] = t('<a href="@url">sirdarckcat</a>', array('@url' => Url::fromUri('http://www.sirdarckcat.net/')));
    $args['%js'] = t('seckit.document_write.js');
    $args['%write'] = t('document.write()');
    $args['%stop'] = t('stop SecKit protection');
    $args['%css'] = t('seckit.no_body.css');
    $args['%display'] = t('display: none');
    $description = t('Enable protection via JavaScript, CSS and Noscript tag. This is the most efficient Clickjacking prevention technique. If webiste is not being framed, %js starts commenting with %write and stops when reaches %stop. Thus %css, which sets body display to none, is ignored. If particularly this JavaScript file is being blocked (with XSS filter of Internet Explorer 8 or Safari), %css sets %display to body. If JavaScript is disabled within browser, it shows a special message. Credits for this trick go to !link.', $args);
    $form['seckit_clickjacking']['js_css_noscript'] = array(
      '#type' => 'checkbox',
      '#title' => t('Enable JavaScript + CSS + Noscript protection'),
      '#return_value' => 1,
      '#default_value' => $config->get('seckit_clickjacking.js_css_noscript'),
      '#description' => $description,
    );

    // custom text for "disabled JavaScript" message
    $form['seckit_clickjacking']['noscript_message'] = array(
      '#type' => 'textfield',
      '#title' => t('Custom text for disabled JavaScript message'),
      '#default_value' => $config->get('seckit_clickjacking.noscript_message'),
      '#description' => t('This message will be shown to user when JavaScript is disabled or unsupported in his browser. Default is "Sorry, you need to enable JavaScript to visit this website."'),
    );

    // main fieldset for SSL/TLS
    $form['seckit_ssl'] = array(
      '#type' => 'details',
      '#title' => t('SSL/TLS'),
      '#collapsible' => TRUE,
      '#tree' => TRUE,
      '#open' => TRUE,
      '#description' => t('Configure various techniques to improve security of SSL/TLS'),
    );
    // enable/disable HTTP Strict Transport Security (HSTS)
    $args['!wiki'] = t('<a href="@url">Wikipedia</a>', array('@url' => Url::fromUri('http://en.wikipedia.org/wiki/HTTP_Strict_Transport_Security')));
    $form['seckit_ssl']['hsts'] = array(
      '#type' => 'checkbox',
      '#title' => t('HTTP Strict Transport Security'),
      '#description' => t('Enable Strict-Transport-Security HTTP response header. HTTP Strict Transport Security (HSTS) header is proposed to prevent eavesdropping and man-in-the-middle attacks like SSLStrip, when a single non-HTTPS request is enough for credential theft or hijacking. It forces browser to connect to the server in HTTPS-mode only and automatically convert HTTP links into secure before sending request. !wiki has more information about HSTS', $args),
      '#default_value' => $config->get('seckit_ssl.hsts'),
    );
    // HSTS max-age directive
    $form['seckit_ssl']['hsts_max_age'] = array(
      '#type' => 'textfield',
      '#title' => t('Max-Age'),
      '#description' => t('Specify Max-Age value in seconds. It sets period when user-agent should remember receipt of this header field from this server. Default is 1000.'),
      '#default_value' => $config->get('seckit_ssl.hsts_max_age'),
    );
    // STS includeSubDomains directive
    $form['seckit_ssl']['hsts_subdomains'] = array(
      '#type' => 'checkbox',
      '#title' => t('Include Subdomains'),
      '#description' => t('Force HTTP Strict Transport Security for all subdomains. If enabled, HSTS policy will be applied for all subdomains, otherwise only for the main domain.'),
      '#default_value' => $config->get('seckit_ssl.hsts_subdomains'),
    );

    // main fieldset for various
    $form['seckit_various'] = array(
      '#type' => 'details',
      '#title' => t('Various'),
      '#collapsible' => TRUE,
      '#tree' => TRUE,
      '#open' => TRUE,
      '#description' => t('Configure various unsorted security enhancements'),
    );

    // enable/disable From-Origin
    $args['!spec'] = t('<a href="@url">specification</a>', array('@url' => Url::fromUri('http://www.w3.org/TR/from-origin/')));
    $form['seckit_various']['from_origin'] = array(
      '#type' => 'checkbox',
      '#title' => t('From-Origin'),
      '#default_value' => $config->get('seckit_various.from_origin'),
      '#description' => t('Enable From-Origin HTTP response header. This forces user-agent to retrieve embedded content from your site only to listed destination. More information is available at !spec page.', $args),
    );
    // From-Origin destination
    $items = array('same - allow loading of content only from your site. Default value.', 'serialized origin - address of trustworthy destination. For example, http://example.com, https://example.com, https://www.example.com, http://www.example.com:8080');
    $args['!items'] = $this->_getItemsList($items);
    $form['seckit_various']['from_origin_destination'] = array(
      '#type' => 'textfield',
      '#title' => t('Allow loading content to'),
      '#default_value' => $config->get('seckit_various.from_origin_destination'),
      '#size' => 90,
      '#description' => t('Trustworthy destination. Possible variants are: !items', $args),
    );
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // if From-Origin is enabled, it should be explicitly set
    $from_origin_enable = $form_state->getValue('seckit_various', 'from_origin');
    $from_origin_destination = $form_state->getValue('seckit_various', 'from_origin_destination');
    if (($from_origin_enable == 1) && (!$from_origin_destination)) {
      form_error($form['seckit_various']['from_origin_destination'], $form_state, t('You have to set up trustworthy destination for From-Origin HTTP response header. Default is same.'));
    }
    // if X-Frame-Options is set to Allow-From, it should be explicitly set
    $x_frame_value = $form_state->getValue('seckit_clickjacking', 'x_frame');
    if ($x_frame_value == SECKIT_X_FRAME_ALLOW_FROM) {
      $x_frame_allow_from = $form_state->getValue('seckit_clickjacking', 'x_frame_allow_from');
      if (!$this->_seckit_explode_value($x_frame_allow_from)) {
        form_error($form['seckit_clickjacking']['x_frame_allow_from'], $form_state, t('You must specify a trusted Origin for the Allow-From value of the X-Frame-Options HTTP response header.'));
      }
    }
    // if HTTP Strict Transport Security is enabled, max-age must be specified.
    // HSTS max-age should only contain digits.
    $hsts_enable = $form_state->getValue('seckit_ssl', 'hsts');
    $hsts_max_age = $form_state->getValue('seckit_ssl', 'hsts_max_age');
    if (($hsts_enable == 1) && (!$hsts_max_age)) {
      form_error($form['seckit_ssl']['hsts_max_age'], $form_state, t('You have to set up Max-Age value for HTTP Strict Transport Security. Default is 1000.'));
    }
    if (preg_match('/[^0-9]/', $hsts_max_age)) {
      form_error($form['seckit_ssl']['hsts_max_age'], $form_state, t('Only digits are allowed in HTTP Strict Transport Security Max-Age field.'));
    }
    // if JS + CSS + Noscript Clickjacking protection is enabled,
    // custom text for disabled JS must be specified
    $js_css_noscript_enable = $form_state->getValue('seckit_clickjacking', 'js_css_noscript');
    $noscript_message = $form_state->getValue('seckit_clickjacking', 'noscript_message');
    if (($js_css_noscript_enable == 1) && (!$noscript_message)) {
      form_error($form['seckit_clickjacking']['noscript_message'], $form_state, t('You have to set up Custom text for disabled JavaScript message when JS + CSS + Noscript protection is enabled.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $list = [];
    $this->buildAttributeList($list, $form_state->getValues());
    $config = $this->config('seckit.settings');

    foreach ($list as $key => $value) {
      $config->set($key, $value);
    }

    $config->save();

    $from_origin_enable = $form_state->getValue('seckit_various', 'from_origin');
    $x_content_type_options_enable = $form_state->getValue('seckit_xss', 'x_content_type', 'checkbox');
    $file_system = file_default_scheme();
    if ($from_origin_enable && ($file_system == 'public')) {
      $msg = 'From-Origin HTTP response header will not be served for files because of public file system. It is recommended to enable private file system to ensure provided by From-Origin security.';
      drupal_set_message($msg, 'warning');
    }
    if ($x_content_type_options_enable && ($file_system == 'public')) {
      $msg = 'X-Content-Type-Options HTTP response header will not be served for files because of public file system. It is recommended to enable private file system to ensure provided by X-Content-Type-Options security.';
      drupal_set_message($msg, 'warning');
    }

    parent::submitForm($form, $form_state);
  }

  /**
   * Build a list from given items.
   */
  public function _getItemsList($items) {
    $list = array(
      '#theme' => 'item_list',
      '#items' => $items,
    );
    return drupal_render($list);
  }

  /**
   * Build the configuration form value list.
   */
  protected function buildAttributeList(
    array &$list = [],
    array $rawAttributes = [],
    $currentName = '')
  {
    foreach ($rawAttributes as $key => $rawAttribute) {
      $name = $currentName ? $currentName . '.' . $key:$key;
      if (in_array($name,['op','form_id','form_token','form_build_id','submit'])){
        continue;
      }
      if (is_array($rawAttribute)) {
        $this->buildAttributeList($list, $rawAttribute, $name);
      } else {
        $list[$name] = $rawAttribute;
      }
    }
  }

  /**
   * Converts a multi-line configuration option to an array.
   * Sanitises by trimming whitespace, and filtering empty options.
   */
  protected function _seckit_explode_value($string) {
    $values = explode("\n", $string);
    return array_values(array_filter(array_map('trim', $values)));
  }
}
