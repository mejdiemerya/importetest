<?php

namespace Drupal\bg_invoice\Controller;

use Dompdf\Dompdf;
use Dompdf\Options;
use Drupal\commerce_order\Entity\Order;
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

    public function generatePdf($invoice_id) {
      $order = Order::load($invoice_id);
      $completed_time = $order->getCompletedTime();
      $date = \DateTime::createFromFormat('U', $completed_time);
      $formatted_date = $date->format('m/d/Y');
      $profile = $order->getBillingProfile();
// Sécuriser l'adresse
      $customer_address = 'Adresse non disponible';
      if ($profile && !$profile->get('address')->isEmpty()) {
        $address = $profile->get('address')->getValue()[0];
        $customer_address = $address['address_line1'] . ' ' . $address['country_code'];
      }



        // Example data (replace with your dynamic data).
        $data = [
            'title' => $order->getItems()[0]->title->getValue()[0]['value'],
            'unit_price' =>'$'. number_format($order->getTotalPrice()->getNumber(), 2, '.', ''),
            'quantity' => '1',
            'total' => '$'.number_format($order->getTotalPrice()->getNumber(), 2, '.', ''),
            'balance_due' => '$0.00',
            'invoice_no' => 1,
            'order_no' => $order->getOrderNumber(),
            'date' => $formatted_date,
            'customer_name' => $order->getCustomer()->field_first_name->getValue()[0]['value'].' '.$order->getCustomer()->field_last_name->getValue()[0]['value'],
            'customer_address' => $customer_address,
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
