<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Usuario;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/")
 */
class UsuarioController extends AbstractController {

    /**
     * @Route("/registro", name="registro")
     */
    public function registro(Request $request, UserPasswordEncoderInterface $encoder) {
        $usuario = new Usuario();
        $form = $this->createFormBuilder($usuario, ['attr' => ['id' => 'registro_form']])
                ->add('email', EmailType::class, array('label' => 'email',
                    'attr' => array('class' => 'form-control2', 'placeholder' => 'Email'),
                    'label_attr' => array('class' => '', 'hidden' => 'true',)))
                ->add('password', PasswordType::class, array('label' => 'password',
                    'attr' => array('class' => 'form-control2', 'placeholder' => 'Password'),
                    'label_attr' => array('class' => '', 'hidden' => 'true',)))
                ->add('nombre', TextType::class, array('label' => 'nombre',
                    'attr' => array('class' => 'form-control2', 'placeholder' => 'Nombre'),
                    'label_attr' => array('class' => '', 'hidden' => 'true',)))
                ->add('apellidos', TextType::class, array('label' => 'apellidos',
                    'attr' => array('class' => 'form-control2', 'placeholder' => 'Apellidos'),
                    'label_attr' => array('class' => '', 'hidden' => 'true',)))
                ->add('telefono', TextType::class, array('label' => 'telefono',
                    'attr' => array('class' => 'form-control2', 'placeholder' => 'Telefono'),
                    'label_attr' => array('class' => '', 'hidden' => 'true',)))
                ->add('foto', FileType::class, array('label' => 'foto',
                    'attr' => array('class' => 'form-control2', 'placeholder' => 'Telefono'),
                    'label_attr' => array('class' => '', 'hidden' => 'true',)))
                ->add('Registrar', SubmitType::class, ['attr' => ['class' => 'btn']])
                ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $usuario = $form->getData();
            //Asignamos un rol temporal
            $usuario->setRoles('ROLE_USER');
            //Codificamos el password
            $password_hash = $encoder->encodePassword($usuario, $usuario->getPassword());
            $usuario->setPassword($password_hash);
            //Guarda el archivo de la foto
            $foto = $form->get('foto')->getData();

            if (!empty($_FILES['form']['name']['foto'])) {
                if ($_FILES['form']['type']['foto'] != 'image/jpeg' &&
                        $_FILES['form']['type']['foto'] != 'image/pjpeg' &&
                        $_FILES['form']['type']['foto'] != 'image/gif' &&
                        $_FILES['form']['type']['foto'] != 'image/png') {
                    $this->addFlash('mensaje', 'El formato de imagen no es el correcto');
                    return $this->render('usuario/registro.html.twig', ['form' => $form->createView()]);
                }
                $nombre = pathinfo($foto->getClientOriginalName(), PATHINFO_FILENAME);
                $nombre_foto = md5(time() + random_int(1, 99999));
                $extension = substr($_FILES['form']['name']['foto'], strrpos($_FILES['form']['name']['foto'], "."));
                move_uploaded_file($_FILES['form']['tmp_name']['foto'], "imagenes/$nombre");

                $origen = "imagenes/$nombre";
                $destino = "imagenes/$nombre_foto$extension";
                $destino_temporal = tempnam("tmp/", "tmp");
                if (redimensionarImagen($origen, $destino_temporal, 100, 100, 100)) {
                    $fp = fopen($destino, "w");
                    fputs($fp, fread(fopen($destino_temporal, "r"), filesize($destino_temporal)));
                    fclose($fp);
                    $usuario->setFoto("$nombre_foto$extension");
                }
            }
            $telefono = $form->get('telefono')->getData();
            if (strlen($telefono) < 9) {
                $this->addFlash('mensaje', 'El teléfono tiene que tener 9 números');
            }
            if (!$this->getDoctrine()->getRepository(Usuario::class)
                            ->findOneBy(['email' => $usuario->getEmail()])) {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($usuario);
                $entityManager->flush();
                $this->addFlash('mensaje', 'Usuario creado');
                return $this->redirectToRoute('app_login');
            } else {
                $this->addFlash('mensaje', 'El email ya existe, por favor introduzca otro.');
            }
        }

        return $this->render('usuario/registro.html.twig', ['formulario_registro' => $form->createView()]);
    }

    /**
     * @Route("/edit", name="usuario_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, UserPasswordEncoderInterface $encoder) {
        $usuario = new Usuario();
        $form = $this->createFormBuilder($usuario, ['attr' => ['id' => 'registro_form']])
                ->add('email', EmailType::class, array('label' => 'email',
                    'attr' => array('class' => 'form-control2', 'placeholder' => 'Email', 'value' => $this->getUser()->getEmail()),
                    'label_attr' => array('class' => '', 'hidden' => 'true',)))
                ->add('password', PasswordType::class, array('label' => 'password',
                    'attr' => array('class' => 'form-control2', 'placeholder' => 'Password'),
                    'label_attr' => array('class' => '', 'hidden' => 'true',)))
                ->add('nombre', TextType::class, array('label' => 'nombre',
                    'attr' => array('class' => 'form-control2', 'placeholder' => 'Nombre', 'value' => $this->getUser()->getNombre()),
                    'label_attr' => array('class' => '', 'hidden' => 'true',)))
                ->add('apellidos', TextType::class, array('label' => 'apellidos',
                    'attr' => array('class' => 'form-control2', 'placeholder' => 'Apellidos', 'value' => $this->getUser()->getApellidos()),
                    'label_attr' => array('class' => '', 'hidden' => 'true',)))
                ->add('telefono', TextType::class, array('label' => 'telefono',
                    'attr' => array('class' => 'form-control2', 'placeholder' => 'Telefono', 'value' => $this->getUser()->getTelefono()),
                    'label_attr' => array('class' => '', 'hidden' => 'true',)))
                ->add('foto', FileType::class, array('label' => 'foto',
                    'attr' => array('class' => 'form-control2'),
                    'label_attr' => array('class' => '', 'hidden' => 'true',)))
                ->add('Modificar', SubmitType::class, ['attr' => ['class' => 'btn']])
                ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $usuario = $form->getData();
            //Codificamos el password
            $password_hash = $encoder->encodePassword($usuario, $usuario->getPassword());
            $usuario->setPassword($password_hash);
            //Guarda el archivo de la foto
            $foto = $form->get('foto')->getData();

            if (!empty($_FILES['form']['name']['foto'])) {
                if ($_FILES['form']['type']['foto'] != 'image/jpeg' &&
                        $_FILES['form']['type']['foto'] != 'image/pjpeg' &&
                        $_FILES['form']['type']['foto'] != 'image/gif' &&
                        $_FILES['form']['type']['foto'] != 'image/png') {
                    $this->addFlash('mensaje', 'El formato de imagen no es el correcto');
                    return $this->render('usuario/registro.html.twig', ['form' => $form->createView()]);
                }
                $nombre = pathinfo($foto->getClientOriginalName(), PATHINFO_FILENAME);
                $nombre_foto = md5(time() + random_int(1, 99999));
                $extension = substr($_FILES['form']['name']['foto'], strrpos($_FILES['form']['name']['foto'], "."));
                move_uploaded_file($_FILES['form']['tmp_name']['foto'], "imagenes/$nombre");

                $origen = "imagenes/$nombre";
                $destino = "imagenes/$nombre_foto$extension";
                $destino_temporal = tempnam("tmp/", "tmp");
                if (redimensionarImagen($origen, $destino_temporal, 100, 100, 100)) {
                    $fp = fopen($destino, "w");
                    fputs($fp, fread(fopen($destino_temporal, "r"), filesize($destino_temporal)));
                    fclose($fp);
                    $usuario->setFoto("$nombre_foto$extension");
                }
            }
            $telefono = $form->get('telefono')->getData();
            if (strlen($telefono) < 9) {
                $this->addFlash('mensaje', 'El teléfono tiene que tener 9 números');
            }
            if ($this->getDoctrine()->getRepository(Usuario::class)
                            ->findOneBy(['email' => $usuario->getEmail()])) {
                $this->getUser()->setNombre($usuario->getNombre());
                $this->getUser()->setEmail($usuario->getEmail());
                $this->getUser()->setApellidos($usuario->getApellidos());
                $this->getUser()->setTelefono($usuario->getTelefono());
                $this->getUser()->setFoto($usuario->getFoto());
                $this->getUser()->setPassword($usuario->getPassword());


                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($this->getUser());
                $entityManager->flush();
                $this->addFlash('mensaje', 'Usuario modificado');
                return $this->redirectToRoute('reserva_index');
            } else {
                $this->addFlash('mensaje', 'El usuario no existe');
            }
        }

        return $this->render('usuario/registro.html.twig', ['formulario_registro' => $form->createView()]);
    }

}
