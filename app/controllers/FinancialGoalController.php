<?Php
// Feito
namespace app\Controllers;

use app\Controllers\Controller;
use app\Models\AuthModel;
use app\Models\FinancialGoalCategory;
use app\Models\FinancialGoalModel;
use app\Models\Priority;
use app\Models\StatusGoal;
use app\Models\FinancialGoalUserModel;
use app\Models\RoleModel;

require "../app/core/functions.php";
session_start();

class FinancialGoalController
{
    private $financialGoalModel;

    public function __construct()
    {
        $this->financialGoalModel = new FinancialGoalModel();
    }

    // Views
    public function showUserFinancialGoals()
    {
        if (AuthModel::checkLogin()) {
            require "../app/core/translate.php";
            $_SESSION['path'] = $_SERVER['REQUEST_URI'];
            $_SESSION['page'] = "manage financial goals";

            $userId = $this->financialGoalModel->id;
            $userCoin = $this->financialGoalModel->coin;
            $userFinancialGoals = $this->financialGoalModel->getUserFinancialGoals($userId);

            $data = [
                "translate" => $translate,
                "userCoin" => $userCoin,
                "userFinancialGoals" => $userFinancialGoals
            ];

            Controller::view("financial-goals/manage-financial-goals", $data);
        }
    }
    public function showAddFinancialGoalForm()
    {
        if (AuthModel::checkLogin()) {
            require "../app/core/translate.php";
            $_SESSION['path'] = $_SERVER['REQUEST_URI'];
            $_SESSION['page'] = "add financial goal";

            $curDate = date("Y-m-d");

            $userCoin = $this->financialGoalModel->coin;

            $statusGoal = StatusGoal::all();
            $financialGoalCategories = FinancialGoalCategory::all();
            $priorities = Priority::all();

            $data = [
                "translate" => $translate,
                "userCoin" => $userCoin,
                "currentDate" => $curDate,
                "financialGoalCategories" => $financialGoalCategories,
                "priorities" => $priorities,
                "statusGoals" => $statusGoal
            ];

            Controller::view("financial-goals/add-financial-goal", $data);
        }
    }
    public function showEditFinancialGoalForm($params)
    {
        if (AuthModel::checkLogin()) {
            require "../app/core/translate.php";
            $_SESSION['path'] = $_SERVER['REQUEST_URI'];
            $_SESSION['page'] = "manage financial goals";
            $currentDate = date("Y-m-d");

            $statusGoal = StatusGoal::all();
            $financialGoalCategorys = FinancialGoalCategory::all();
            $priorities = Priority::all();

            $userCoin = $this->financialGoalModel->coin;
            $userId = $this->financialGoalModel->id;

            $financialGoalId = $params->id;

            $financialGoal = $this->financialGoalModel->getFinancialGoalByUserPermission($financialGoalId, $userId, "edit_financial_goal");

            $data = [
                "financialGoalCategorys" => $financialGoalCategorys,
                "priorities" => $priorities,
                "statusGoals" => $statusGoal,
                "curDate" => $currentDate,
                "translate" => $translate,
                "financialGoal" => $financialGoal,
                "userCoin" => $userCoin
            ];

            Controller::view("financial-goals/edit-financial-goal", $data);
        }
    }

    // Backend
    public function addFinancialGoal($params)
    {
        if (AuthModel::checkLogin()) {
            $financialGoalValue = $params->value;
            $financialGoalName = $params->name;
            $category = $params->category;
            $startDate = $params->start_date;
            $finalDate = $params->final_date;
            $status = $params->status;
            $priority = $params->priority;

            $financialGoalId = $this->financialGoalModel->addFinancialGoal($financialGoalValue, $financialGoalName, $category, $startDate, $finalDate, $status, $priority);

            if ($financialGoalId) {
                $userId = $this->financialGoalModel->id;

                $roleInstance = new RoleModel();
                $financialGoalUserInstance = new FinancialGoalUserModel();

                $roleId = $roleInstance->getRoleIdByName("creator");

                if ($financialGoalUserInstance->addUserFinancialGoal($userId, $financialGoalId, $roleId))
                    echo 1;
            } else
                echo 0;
        }
    }
    public function updateFinancialGoal($params)
    {
        if (AuthModel::checkLogin()) {
            $financialGoalId = $params->id;
            $userId = $this->financialGoalModel->id;

            $financialGoalUserInstance = new FinancialGoalUserModel;

            $userRole = $financialGoalUserInstance->getUserRole($financialGoalId, $userId);

            if (hasPerm($userRole, "edit_financial_goal")) {

                parse_str(file_get_contents("php://input"), $_PUT);

                $financialGoalValue = $_PUT["value"];
                $financialGoalName = $_PUT["name"];
                $category = $_PUT["category"];
                $startDate = $_PUT["start_date"];
                $finalDate = $_PUT["final_date"];
                $status = $_PUT["status"];
                $priority = $_PUT["priority"];

                if ($financialGoalId = $this->financialGoalModel->updateFinancialGoal($financialGoalId, $financialGoalValue, $financialGoalName, $category, $startDate, $finalDate, $status, $priority))
                    echo 1;
                else
                    echo 0;
            } else
                echo 0;
        }
    }
    public function deleteFinancialGoal($params)
    {
        if (AuthModel::checkLogin()) {
            $financialGoalId = $params->id;
            $userId = $this->financialGoalModel->id;

            $financialGoalUserInstance = new FinancialGoalUserModel();

            $userRole = $financialGoalUserInstance->getUserRole($financialGoalId, $userId);

            if (hasPerm($userRole, "delete_financial_goal")) {
                if ($this->financialGoalModel->deleteFinancialGoal($financialGoalId))
                    echo 1;
                else
                    echo 0;
            } else
                echo 0;
        }
    }
}