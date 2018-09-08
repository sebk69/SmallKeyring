<?php
/**
 *  This file is a part of SmallKeyring
 *  Copyright 2018 - Sébastien Kus
 *  Under GNU GPL V3 licence
 */

namespace App\Controller;


use App\Bundle\SmallKeyringModelBundle\Model\Key;
use App\Form\KeyDeleteType;
use App\Form\KeyType;
use App\Key\KeyDeleteForm;
use App\Security\KeyVoter;
use App\Security\UserVoter;
use Sebk\SmallOrmBundle\Factory\Dao;
use Sebk\SmallUserBundle\Security\Invite;
use Sebk\SmallUserBundle\Security\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class KeyController
 * @package App\Controller
 * @route("/home/key")
 */
class KeyController extends Controller
{
    /**
     * @route("/create", methods={"GET"}, name="key_create")
     */
    public function getCreateKey(Request $request)
    {
        $key = $this->get("sebk_small_orm_dao")->get("BundleSmallKeyringModelBundle", "Key")->newModel();

        // Security
        try {
            $this->denyAccessUnlessGranted(KeyVoter::CREATE, $key);
        } catch (AccessDeniedException $e) {
            return $this->redirectToRoute("home");
        }

        // Create form
        $form = $this->createForm(KeyType::class, $key);

        /** @var \App\Bundle\SmallKeyringModelBundle\Dao\Key $dao */
        $dao = $this->get("sebk_small_orm_dao")->get("BundleSmallKeyringModelBundle", "Key");
        $modelList = $dao->listKeys($this->getUser()->getId());

        // Render
        return $this->render('key/form.html.twig', [
            "form" => $form->createView(),
            "list" => $modelList,
            "keyId" => "new",
            "user" => $this->getUser(),
        ]);
    }

    /**
     * @route("/edit/{id}", methods={"GET"}, name="key_edit")
     */
    public function getEditKey($id, Request $request)
    {
        $key = $this->get("sebk_small_orm_dao")->get("BundleSmallKeyringModelBundle", "Key")->findOneBy(["id" => $id]);

        // Security
        try {
            $this->denyAccessUnlessGranted(KeyVoter::EDIT, $key);
        } catch (AccessDeniedException $e) {
            return $this->redirectToRoute("home");
        }

        // Create form
        $form = $this->createForm(KeyType::class, $key);

        $keyDelete = new KeyDeleteForm();
        $keyDelete->setIdKey($key->getId());
        $keyDeleteForm = $this->createForm(KeyDeleteType::class, $keyDelete);

        /** @var \App\Bundle\SmallKeyringModelBundle\Dao\Key $dao */
        $dao = $this->get("sebk_small_orm_dao")->get("BundleSmallKeyringModelBundle", "Key");
        $modelList = $dao->listKeys($this->getUser()->getId());

        // Render
        return $this->render('key/form.html.twig', [
            "form" => $form->createView(),
            "formDelete" => $keyDeleteForm->createView(),
            "list" => $modelList,
            "keyId" => $key->getId(),
            "url" => $key->getUrlCompleted(),
            "user" => $this->getUser(),
        ]);
    }

    /**
     * Persist key form
     * @param Form $form
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function persistKey(Form $form)
    {
        // If form is valid
        /** @var Key $key */
        $key = $form->getData();

        if($key->fromDb) {
            $keyDelete = new KeyDeleteForm();
            $keyDelete->setIdKey($key->getId());
            $keyDeleteForm = $this->createForm(KeyDeleteType::class, $keyDelete);
        } else {
            $keyDelete = null;
            $keyDeleteForm = null;
        }

        /** @var \App\Bundle\SmallKeyringModelBundle\Dao\Key $dao */
        $dao = $this->get("sebk_small_orm_dao")->get("BundleSmallKeyringModelBundle", "Key");
        $modelList = $dao->listKeys($this->getUser()->getId());

        if ($form->isSubmitted() && $form->isValid()) {
            // Validate key
            $key->setUserId($this->getUser()->getId());
            if(!$key->getValidator()->validate()) {
                // Render failure
                return $this->render('key/form.html.twig', [
                    "form" => $form->createView(),
                    "formDelete" => $keyDeleteForm->createView(),
                    "error" => $key->getValidator()->getMessage(),
                    "success" => false,
                    "list" => $modelList,
                    "url" => $key->getUrlCompleted(),
                    "user" => $this->getUser(),
                    "keyId" => $key->getId() === null ? "new" : $key->getId(),
                ]);
            }

            // Persist on validation success
            $key->persist();
        } else {
            // not valid
            return $this->render('key/form.html.twig', [
                "form" => $form->createView(),
                "formDelete" => $keyDeleteForm->createView(),
                "error" => "",
                "success" => false,
                "list" => $modelList,
                "url" => $key->getUrlCompleted(),
                "user" => $this->getUser(),
                "keyId" => $key->getId() === null ? "new" : $key->getId(),
            ]);
        }

        // Redirect to home
        return $this->redirectToRoute("key_edit", ["id" => $key->getId()]);
    }

    /**
     * @route("/create", methods={"POST"}, name="post_key_create")
     */
    public function postCreateKey(Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // Handle request form
        /** @var Key $key */
        $key = $this->get("sebk_small_orm_dao")->get("BundleSmallKeyringModelBundle", "Key")->newModel();
        $form = $this->createForm(KeyType::class, $key);
        $form->handleRequest($request);

        // Security
        try {
            $this->denyAccessUnlessGranted(KeyVoter::CREATE, $key);
        } catch (AccessDeniedException $e) {
            return $this->redirectToRoute("home");
        }

        return $this->persistKey($form);
    }

    /**
     * @route("/delete", methods={"POST"}, name="key_delete")
     */
    public function deleteKey(Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // Handle request
        $keyDelete = new KeyDeleteForm();
        $form = $this->createForm(KeyDeleteType::class, $keyDelete);
        $form->handleRequest($request);

        if($form->isValid() && $form->isSubmitted()) {
            /** @var \App\Bundle\SmallKeyringModelBundle\Dao\Key $dao */
            $dao = $this->get("sebk_small_orm_dao")->get("BundleSmallKeyringModelBundle", "Key");
            $key = $dao->findOneBy(["id" => $keyDelete->getIdKey()]);

            // Security
            try {
                $this->denyAccessUnlessGranted(KeyVoter::EDIT, $key);
            } catch (AccessDeniedException $e) {
                return $this->redirectToRoute("home");
            }

            $key->delete();
            return $this->redirectToRoute("key_list");
        }

        return $this->redirectToRoute("home");
    }

    /**
     * @route("/edit/{id}", methods={"POST"}, name="post_key_edit")
     */
    public function postEditKey($id, Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // Handle request form
        /** @var Key $key */
        $key = $this->get("sebk_small_orm_dao")->get("BundleSmallKeyringModelBundle", "Key")->findOneBy(["id" => $id]);
        $form = $this->createForm(KeyType::class, $key);
        $form->handleRequest($request);

        // Security
        try {
            $this->denyAccessUnlessGranted(KeyVoter::CREATE, $key);
        } catch (AccessDeniedException $e) {
            return $this->redirectToRoute("home");
        }

        return $this->persistKey($form);
    }

    /**
     * @route("/list", methods={"GET"}, name="key_list")
     */
    public function listKeys(Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        /** @var \App\Bundle\SmallKeyringModelBundle\Dao\Key $dao */
        $dao = $this->get("sebk_small_orm_dao")->get("BundleSmallKeyringModelBundle", "Key");
        $modelList = $dao->listKeys($this->getUser()->getId());

        return $this->render('key/list.html.twig', [
            'list' => $modelList,
            "user" => $this->getUser(),
        ]);
    }

    /**
     * @route("/", methods={"GET"}, name="key_base")
     */
    public function base()
    {
        return $this->redirectToRoute("key_list");
    }
}