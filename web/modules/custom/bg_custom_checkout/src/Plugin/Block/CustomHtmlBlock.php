<?php

namespace Drupal\bg_custom_checkout\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'Custom HTML Block' block.
 *
 * @Block(
 *   id = "custom_html_block",
 *   admin_label = @Translation("Custom HTML Block"),
 * )
 */
class CustomHtmlBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#type' => 'markup',
      '#markup' => '
        <div class="col-md-4 col-lg-3" data-gtm-vis-has-fired7025260_75="1">
          <div class="well" data-gtm-vis-has-fired7025260_75="1">
            <h5 class="mt0" data-gtm-vis-has-fired7025260_75="1">Money-Back Guarantee</h5>
            <p class="small" data-gtm-vis-has-fired7025260_75="1">We want you to be completely satisfied with your purchase. If you are dissatisfied for any reason, right up to the last day of your subscription or membership term, you may cancel and receive a 100% refund of your most recent payment.</p>
          </div>
          <img class="img-responsive mt20 mb20" src="/sites/default/files/images/USGBC-education-partner_gray.png" data-gtm-vis-has-fired7025260_75="1">
          <div class="well well-transparent small" data-gtm-vis-has-fired7025260_75="1">
            <p class="mb05" data-gtm-vis-has-fired7025260_75="1">“I don\'t have time to mess around, so I always go straight to BuildingGreen for up-to-date and thoughtful insights on green products, technologies, and design solutions. My BuildingGreen membership is priceless.”</p>
            <p class="text-right line-snug mt0" data-gtm-vis-has-fired7025260_75="1">
              <small data-gtm-vis-has-fired7025260_75="1"><em data-gtm-vis-has-fired7025260_75="1">—David Gottfried<br data-gtm-vis-has-fired7025260_75="1">CEO, Regenerative Ventures<br data-gtm-vis-has-fired7025260_75="1">Founder, USGBC</em></small>
            </p>
            <p class="mb05" data-gtm-vis-has-fired7025260_75="1">“LEEDuser is the best investment I make for my LEED projects.”</p>
            <p class="text-right line-snug mt0" data-gtm-vis-has-fired7025260_75="1">
              <small data-gtm-vis-has-fired7025260_75="1"><em data-gtm-vis-has-fired7025260_75="1">—David L. Sheridan, Ph.D., P.E., LEED AP BD+C<br data-gtm-vis-has-fired7025260_75="1">Aqua Cura</em></small>
            </p>
          </div>
          <div class="AuthorizeNetSeal" data-gtm-vis-has-fired7025260_75="1">
            <script type="text/javascript" language="javascript" data-gtm-vis-has-fired7025260_75="1">var ANS_customer_id="78c07784-b5af-4c6a-9aca-f6dc27c7f0ab";</script>
            <script type="text/javascript" language="javascript" src="//verify.authorize.net/anetseal/seal.js" data-gtm-vis-has-fired7025260_75="1"></script>
            <style type="text/css" data-gtm-vis-has-fired7025260_75="1">
              div.AuthorizeNetSeal{text-align:center;margin:0;padding:0;width:90px;font:normal 9px arial,helvetica,san-serif;line-height:10px;}
              div.AuthorizeNetSeal a{text-decoration:none;color:black;}
              div.AuthorizeNetSeal a:visited{color:black;}
              div.AuthorizeNetSeal a:active{color:black;}
              div.AuthorizeNetSeal a:hover{text-decoration:underline;color:black;}
              div.AuthorizeNetSeal a img{border:0px;margin:0px;text-decoration:none;}
            </style>
            <a href="https://verify.authorize.net/anetseal/?pid=78c07784-b5af-4c6a-9aca-f6dc27c7f0ab&amp;rurl=https://leeduser.buildinggreen.com" onmouseover="window.status=\'http://www.authorize.net/\'; return true;" onmouseout="window.status=\'\'; return true;" onclick="window.open(\'https://verify.authorize.net/anetseal/?pid=78c07784-b5af-4c6a-9aca-f6dc27c7f0ab&amp;rurl=https://leeduser.buildinggreen.com\',\'AuthorizeNetVerification\',\'width=600,height=430,dependent=yes,resizable=yes,scrollbars=yes,menubar=no,toolbar=no,status=no,directories=no,location=yes\'); return false;" rel="noopener noreferrer" target="_blank" data-gtm-vis-has-fired7025260_75="1">
              <img src="https://verify.authorize.net/anetseal/images/secure90x72.gif" width="90" height="72" border="0" alt="Authorize.Net Merchant - Click to Verify" data-gtm-vis-has-fired7025260_75="1">
            </a>
          </div>
        </div>
      ',
      '#allowed_tags' => ['div', 'h5', 'p', 'img', 'script', 'style', 'a', 'br', 'small', 'em'], // Sécurité pour HTML
    ];
  }
}
