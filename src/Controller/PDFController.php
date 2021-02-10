<?php

namespace App\Controller;
use App\Entity\Reserva;
use App\Entity\Pista;
use App\Entity\Horas;
use App\Entity\Usuario;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

// Include Dompdf required namespaces
use Dompdf\Dompdf;
use Dompdf\Options;

class PDFController extends AbstractController
{
    /**
     * @Route("/pdf", name="pdf_generar")
     */
    public function pdfGenerar(Request $request)
    {
        // Configure Dompdf according to your needs
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        
        // Instantiate Dompdf with our options
        $dompdf = new Dompdf($pdfOptions);
        
        $fecha = $request->request->get('fecha');
        $fecha = "12-02-2020";
        $fecha_db = new \DateTime(str_replace("/", "-", $fecha));
        $reservas = $this->getDoctrine()->getRepository(Reserva::class)->findBy(['fecha' => $fecha_db]);

        // Retrieve the HTML generated in our twig file
        $html= $this->renderView('lista_pdf.html.twig', [
            'reservas' => $reservas
        ]);
        
        // Load HTML to Dompdf
        $dompdf->loadHtml($html);
        
        // (Optional) Setup the paper size and orientation 'portrait' or 'portrait'
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser (force download)
        $dompdf->stream("mypdf.pdf", [
            "Attachment" => false
        ]);
    }
}
