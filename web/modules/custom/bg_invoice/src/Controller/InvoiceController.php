<?php

namespace Drupal\bg_invoice\Controller;

use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Controller\ControllerBase;

class InvoiceController extends ControllerBase {

    protected $dompdf;

    public function __construct(Dompdf $dompdf) {
        $this->dompdf = $dompdf;
    }

    public static function create(ContainerInterface $container) {
        return new static(
            $container->get('bg_invoice.dompdf')
        );
    }

    public function generatePdf() {
        // Example data (replace with your dynamic data).
        $data = [
            'title' => 'LEEDuser Premium Monthly Subscription Subscription Individual (LUPRM-MI)',
            'unit_price' => '$15.95',
            'quantity' => '1',
            'total' => '$15.95',
            'balance_due' => '$0.00',
            'invoice_no' => 1,
            'order_no' => '212874',
            'date' => date('F j, Y'),
            'customer_name' => 'Chris DeJulis',
            'customer_address' => '8 N Jay St, Middleburg, VA 20117, United States',
        ];

        // Render the HTML using Twig.
        $html = \Drupal::service('twig')->render('modules/custom/bg_invoice/templates/invoice.html.twig', $data);

        // Configure Dompdf.
        $options = new Options();
        $options->set('defaultFont', 'Helvetica');
      $options->set('isRemoteEnabled', true);
        $this->dompdf->setOptions($options);

        // Load HTML.
        $this->dompdf->loadHtml($html);
        $this->dompdf->setPaper('A4', 'portrait');
        $this->dompdf->render();
      $pdfOutput = $this->dompdf->output();

        // Stream PDF file.
      return new \Symfony\Component\HttpFoundation\Response($pdfOutput, 200, [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' => 'inline; filename="invoice-1.pdf"',
      ]);

    }
}
