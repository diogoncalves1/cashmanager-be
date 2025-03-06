<?php

namespace app\Controllers;

use app\Controllers\Controller;
use app\Models\Category;
use Exception;

class CategoryController
{
    private $categoryInstance;

    function __construct()
    {
        $this->categoryInstance = new Category();
    }

    function index()
    {
        $categorys = $this->categoryInstance->getCategory();
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
        $id = $_GET["i"];
        include("../backend/querys.php");
        $code = get_code_cat($conn, $id);
        if (isset($p->sub_cat))
            $sc = 1;
        else
            $sc = 0;
        update_category($conn, $code, $p->PT, $p->EN, $sc, $id);
        header("location: /CashManager/public/admin/categories-expenses");
    }
    public function delete()
    {
        $categoryId = $_POST["id"];
        $categoryCode = $this->categoryInstance->getCode($categoryId);
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

        $this->categoryInstance::add($conditions, $values);
        $xml = simplexml_load_file("../backend/translate.xml");
        $word = $xml->addChild("word");

        $word->addChild("name", $p->code);
        $word->addChild("PT", $p->PT);
        $word->addChild("EN", $p->EN);

        $xml->asXML("../backend/translate.xml");

        header("location: /CashManager/admin/categories-expenses");
    }
}
