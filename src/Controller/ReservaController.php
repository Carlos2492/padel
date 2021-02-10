<?php

namespace App\Controller;

use App\Entity\Reserva;
use App\Entity\Pista;
use App\Entity\Horas;
use App\Form\ReservaType;
use App\Repository\ReservaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("/reserva")
 */
class ReservaController extends AbstractController
{
    /**
     * @Route("/", name="reserva_index", methods={"GET"})
     */
    
    public function ver_reservas()
    {
        return $this->render('menu.html.twig',['usuario'=>$this->getUser()]);
        
    }
    
    /**
     * @Route("/datepicker", name="reserva_datepicker", methods={"GET"})
     */
    
    public function ver_datepicker()
    {
        return $this->render('reserva/mostrar_reservas.html.twig',['usuario'=>$this->getUser()]);
        
    }
    
    /**
     * @Route("/usuario_reservas", name="reservas_user", methods={"GET"})
     */
    public function reservas_user() {
        $reserva = $this->getDoctrine()->getRepository(Reserva::class)->findBy(['usuario' => $this->getUser()]);
        return $this->render('reserva/reservas_usuario.html.twig', ['reservas' => $reserva, 'usuario'=>$this->getUser()]);
    }
    
    /**
     * @Route("/admin_reservas", name="admin_reservas", methods={"GET"})
     */
    public function admin_reservas() {
        $reserva = $this->getDoctrine()->getRepository(Reserva::class)->findAll();
        return $this->render('reserva/admin_reservas.html.twig', ['reservas' => $reserva, 'usuario'=>$this->getUser()]);
    }
    
    /**
     * @Route("/load_table", name="cargar_tabla", methods={"POST"})
     */
    public function cargar_tabla(Request $request): Response
    {
        $fecha = $request->request->get('fecha');
        $fecha_db = new \DateTime(str_replace("/", "-", $fecha));
        $reservas = $this->getDoctrine()->getRepository(Reserva::class)->findBy(['fecha'=>$fecha_db]);
        $pistas = $this->getDoctrine()->getRepository(Pista::class)->findAll();
        $horas = $this->getDoctrine()->getRepository(Horas::class)->findAll();
        return $this->render('reserva/tabla_reservas.html.twig',['reservas' => $reservas , 'pistas' => $pistas, 'horas' =>$horas, 'fecha' =>$fecha, 'usuario'=>$this->getUser()]);
        
    }

   
    /**
     * @Route("/reserva_new", name="reserva_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {   
        $usuario = $this->getUser();
        $fecha = $request->request->get('fecha');
        $fecha_db = new \DateTime(str_replace("/", "-", $fecha));
        
        $existe = $this->getDoctrine()->getRepository(Reserva::class)->findOneBy(
                ['hora'=>$request->request->get('hora'),
                   'pista'=>$request->request->get('pista'),
                    'fecha'=>$fecha_db]);
        
        if($existe==null){
          $pista = $this->getDoctrine()->getRepository(Pista::class)->findOneBy(['num_pista'=>$request->request->get('pista')]);
          $hora = $this->getDoctrine()->getRepository(Horas::class)->findOneBy(['hora'=>$request->request->get('hora')]);

          $reserva = new reserva();
          $reserva->setPista($pista); 
          $reserva->setFecha($fecha_db);
          $reserva->setHora($hora);
          $reserva->setUsuario($usuario);

          $entityManager = $this->getDoctrine()->getManager();
          $entityManager->persist($reserva);
          $entityManager->flush();
          
          $response = new JsonResponse();
          $response->setData(['resultado'=>true]);
          return $response;  
            
        }else{
          $response = new JsonResponse();
          $response->setData(['resultado'=>false]);
          return $response;  
        }
        
        
    }
    
    
    /**
     * @Route("/cargar_reservas", name="cargar_reservas", methods={"POST"})
     */
    public function cargar_reservas(Request $request): Response {
        $fecha = $request->request->get('fecha');
        $fecha_db = new \DateTime(str_replace("/", "-", $fecha));
        $reservas = $this->getDoctrine()->getRepository(Reserva::class)->findBy(['fecha' => $fecha_db]);
        return $this->render('reserva/lista_reservas_admin.html.twig', ['reservas' => $reservas, 'usuario'=>$this->getUser()]);
    }

    /**
     * @Route("/{id}", name="reserva_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Reserva $reserva): Response {
        $fechares = $reserva->getFecha();
        $fechaact = new \DateTime();
        $diferencia = $fechares->diff($fechaact);
        if ($diferencia->days > 1) {
            if ($this->isCsrfTokenValid('delete' . $reserva->getId(), $request->request->get('_token'))) {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->remove($reserva);
                $entityManager->flush();
            }
        } else {
            $this->addFlash('mensaje', 'No puedes anular la reserva, faltan menos de 24h.');
        }
        return $this->redirectToRoute('reservas_user',['usuario'=>$this->getUser()]);
    }
}
