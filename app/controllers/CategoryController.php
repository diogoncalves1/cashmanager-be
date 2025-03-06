<?php

namespace app\Controllers;

use app\Controllers\Controller;
use app\Models\Category;
use Exception;

class CategoryController
{
    private $categoryModel;

    function __construct()
    {
        $this->categoryModel = new Category();
    }

    function index()
    {
        $categorys = $this->categoryModel->getCategory();
        Controller::view("admin/cats-expenses", ["categorys" => $categorys]);
    }
    function add()
    {
        Controller::view("admin/cats-expenses-add");
    }
    function edit()
    {
        Controller::view("admin/cats-expenses-edit");
    }
    function update($p)
    {
        $categoryId = $_GET["i"];
        $code = $this->categoryModel->getCode($categoryId);

        if (isset($p->sub_cat))
            $sc = 1;
        else
            $sc = 0;

        $this->categoryModel->updateCategory($sc, $categoryId);

        $url = "../backend/translate.xml";
        $xml = simplexml_load_file($url);

        foreach ($xml as $word) {
            if ($word->name == $code) {
                $word->PT = $p->PT;
                $word->EN = $p->EN;
            }
        }
        $xml->asXML($url);

        header("location: /CashManager/public/admin/categories-expenses");
    }
    public function delete()
    {
        $categoryId = $_POST["id"];
        $categoryCode = $this->categoryModel->getCode($categoryId);
        $response = Category::delete($categoryId);

        $xml = simplexml_load_file("../backend/translate.xml");

        try {
            foreach ($xml as $word) {
                if ($word->name == $categoryCode) {
                    unset($word[0]);
                    break;
                }
            }
            if (!$xml->asXML("../backend/translate.xml"))
                throw new Exception("Erro na validação do xml");
        } catch (Exception $e) {
            echo 0;
        }

        echo $response;
    }
    public function store($p)
    {
        $conditions = ["code", "`sub-category`"];
        $values = [$p->code, isset($p->sub_cat) ?: 0];

        $this->categoryModel::add($conditions, $values);
        $xml = simplexml_load_file("../backend/translate.xml");
        $word = $xml->addChild("word");

        $word->addChild("name", $p->code);
        $word->addChild("PT", $p->PT);
        $word->addChild("EN", $p->EN);

        $xml->asXML("../backend/translate.xml");

        header("location: /CashManager/admin/categories-expenses");
    }
}