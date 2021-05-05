<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\TitulacionesAlcanzadas;
use App\Entity\GraduadoSoporte;
use App\Entity\Graduado;
use App\Entity\TitulacionesAlcanzadasOrig;
use App\Form\GraduadoPersonalesType;
use App\Form\GraduadoPersonalesOrigType;
use App\Form\TitulacionesGraduadoType;
use App\Form\GraduadoTitulacionesType;
use App\Services\ConsultaBD;
use App\Services\FuncionesGraduado;
use App\Entity\UserBusqueda;
use App\Form\ImagenGraduadoOrigType;
use App\Form\ImagenGraduadoType;
use App\Form\UserBusquedaType;

class GraduadoController extends AbstractController
{
    ############### ABM ##################

    /**
     * @Route("admin/datosPersonalesGraduado", name="datosPersonalesGraduado")
     */
    public function datosPersonalesGraduado(Request $request)
    {
        $graduado = new GraduadoSoporte();


        $formulario = $this ->createForm(GraduadoPersonalesType::class, $graduado);
        $formulario -> handleRequest($request);

        if($formulario -> isSubmitted() && $formulario -> isValid()){
            $em = $this -> getDoctrine() -> getManager();
            $em -> persist($graduado);
            $em -> flush();

            return $this -> redirectToRoute('imagenGraduado', ['id' => $graduado->getId()]); //Lo redirijo a la solapa siguiente
        }


        return $this -> render('graduado/datosPersonalesGraduado.html.twig', [
            'formulario' => $formulario -> createView()
        ]);
    }

    /**
     * @Route("admin/imagenGraduado/{id}", name="imagenGraduado")
     */
    public function imagenGraduado(Request $request, $id){
        $em = $this -> getDoctrine() -> getManager();
        $funcionesGraduado = new FuncionesGraduado();
        $graduado = $em -> getRepository(GraduadoSoporte::class) -> find($id);
     
       

        $formulario = $this -> createForm(ImagenGraduadoType::class, $graduado);
        $formulario -> handleRequest($request);

        if($formulario -> isSubmitted() && $formulario -> isValid() && $funcionesGraduado -> cargarImagenGraduado($formulario)){
            
            $em -> persist($graduado);
            $em -> flush();

            return $this -> redirectToRoute('titulacionesGraduado', ['id' => $graduado->getId()]); //Lo redirijo a la solapa siguiente
        }
        return $this->render('graduado/imagenGraduado.html.twig', [
            'formulario' => $formulario->createView(),
        ]);
    }


     /**
     * @Route("admin/titulacionesGraduado/{id}", name="titulacionesGraduado")
     */
    public function titulacionesGraduado(Request $request, $id){
        $em = $this -> getDoctrine() -> getManager();

        $funcionesGraduado = new FuncionesGraduado();
        $titulaciones = new TitulacionesAlcanzadas();
        $graduado = $em -> getRepository(GraduadoSoporte::class) -> find($id);
        
        
        $formulario = $this->createForm(GraduadoTitulacionesType::class, $graduado);
        $formulario -> handleRequest($request);

        $formularioTitulacion = $this ->createForm(TitulacionesGraduadoType::class, $titulaciones);
        $formularioTitulacion -> handleRequest($request);

        
        if($formulario -> isSubmitted() && $formulario -> isValid()){

            $em -> persist($graduado);

            $graduadoOriginal = new Graduado();
            $graduadoOriginal = $funcionesGraduado->copiarGraduado($graduadoOriginal,$graduado);
            
            //Arriba copio todos los datos del egresado soporte en el original y las titulaciones las cargo
            //directamente al original.
            $graduadoOriginal -> addTitulacione($titulaciones);
            $em -> persist($graduadoOriginal);
            $em -> remove($graduado);
            $em -> flush();

            return $this -> redirectToRoute('verGraduados');
        }

        return $this -> render('graduado/titulacionesGraduado.html.twig', [
            'formulario' => $formulario -> createView(), 'formularioTitulacion' => $formularioTitulacion -> createView()
        ]);
    }


    /**
     * @Route("admin/verGraduados", name="verGraduados")
     */
    public function verGraduados(Request $request)
    {
        $em = $this -> getDoctrine() -> getManager();
        
        $form = $this->createForm(UserBusquedaType::class,new UserBusqueda());
        $form->handleRequest($request);
        $busqueda=$form->getData();

        $consultaBD = new ConsultaBD();

        $graduados = $em -> getRepository(Graduado::class)->findBy(array(), array('apellido' => 'DESC'));

        if ($form->isSubmitted()){
            return $this->render('graduado/verGraduados.html.twig', [
            'graduados' => $consultaBD->consultaTodos($busqueda, $em),'form' => $form->createView()
        ]);
        }
        else{
            return $this -> render('graduado/verGraduados.html.twig', [
                'graduados' => $graduados,'form' => $form->createView()
            ]);
        }
       
    }

    /**
     * @Route("admin/modificarDatosPersonalesGraduado/{id}", name="modificarDatosPersonalesGraduado")
     */
    public function modificarDatosPersonalesGraduado(Request $request, $id)
    {
        $em = $this -> getDoctrine() -> getManager();
        $graduado = $em -> getRepository(Graduado::class) -> find($id);
        

        $formulario = $this -> createForm(GraduadoPersonalesOrigType::class, $graduado);
        $formulario -> handleRequest($request);

        if($formulario -> isSubmitted() && $formulario -> isValid()){
            $em -> flush();
            return $this -> redirectToRoute('verGraduados');
        }
        
        return $this -> render('graduado/modificarDatosPersonalesGraduado.html.twig', [
            'formulario' => $formulario -> createView()
        ]);
        
    }


     /**
     * @Route("admin/modificarImagenGraduado/{id}", name="modificarImagenGraduado")
     */
    public function modificarImagenGraduado(Request $request, $id){
        $em = $this -> getDoctrine() -> getManager();
        $graduado = $em -> getRepository(Graduado::class) -> find($id);
        $funcionesGraduado = new FuncionesGraduado();
        $urlImagen = $graduado->getImagenTED();
         
        
        $formulario = $this -> createForm(ImagenGraduadoOrigType::class, $graduado);
        $formulario -> handleRequest($request);

        if($formulario -> isSubmitted() && $funcionesGraduado -> modificarImagenGraduado($formulario, $urlImagen)){
            $em -> persist($graduado);
            $em -> flush();

            return $this -> redirectToRoute('verGraduados');
        }

        return $this -> render('graduado/modificarImagenGraduado.html.twig', [
            'formulario' => $formulario -> createView(),
            'imagen' => $urlImagen,
            'graduado' => $graduado
        ]);
    }
    
    /**
     * @Route("/admin/verImagenTED/{id}", name="verImagenTED")
     */
    public function verImagenTED($id)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $graduado= $entityManager->getRepository(Graduado::class)->find($id);

        return $this->redirect("https://intranet.unraf.edu.ar/RegistroDigital/uploads/imagenTED/" . $graduado->getImagenTED());
    }

    /**
     * @Route("/admin/eliminarImagenTED/{id}", name="eliminarImagenTED")
     */
    public function eliminarImagenTED($id){
        $entityManager = $this->getDoctrine()->getManager();

        $graduado = $entityManager->getRepository(Graduado::class)->find($id);

        unlink('uploads/imagenTED/' . $graduado -> getImagenTED());
        $graduado ->setImagenTED(null);
        //Se setea a nulo porque el remove en este caso no sirve, se utiliza para casos como para borrar
        //la entidad o para borrar un objeto
        $entityManager->flush();

        
        return $this -> redirectToRoute('modificarImagenGraduado', ['id' => $id]);

    }



       /**
     * @Route("admin/eliminarGraduado/{id}", name="eliminarGraduado")
     */
    public function eliminarGraduado()
    {
        
    }


    
   
}