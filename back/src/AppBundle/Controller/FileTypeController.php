<?php
/**
 * Created by PhpStorm.
 * User: Nicolas
 * Date: 05/10/2017
 * Time: 17:38
 */

namespace AppBundle\Controller;


use AppBundle\Repository\FileTypeRepository;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\FOSRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class FileTypeController
 * @package AppBundle\Controller
 * @RouteResource("FileType")
 */
class FileTypeController extends FOSRestController
{
    /**
     * @return Response
     * @Security("is_granted('ROLE_TEACHER')")
     */
    public function cgetAction()
    {
        $fileTypes = $this->getDoctrine()
            ->getRepository('AppBundle:FileType')
            ->findBy(array(), array('type' => 'asc'));

        return new Response($this->get('serializer')->serialize($fileTypes, 'json'));
    }

    /**
     * @return Response
     */
    public function cgetImagesAction()
    {
        /** @var FileTypeRepository $fileTypeRepository */
        $fileTypeRepository = $this->getDoctrine()->getRepository('AppBundle:FileType');

        $fileTypes = $fileTypeRepository->findImageTypes();

        return new Response($this->get('serializer')->serialize($fileTypes, 'json'));
    }

    /**
     * @return Response
     */
    public function cgetCsvAction()
    {
        /** @var FileTypeRepository $fileTypeRepository */
        $fileTypeRepository = $this->getDoctrine()->getRepository('AppBundle:FileType');

        $fileTypes = $fileTypeRepository->findCsvType();

        return new Response($this->get('serializer')->serialize($fileTypes, 'json'));
    }
}