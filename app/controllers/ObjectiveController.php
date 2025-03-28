<?php
// Feito
namespace app\Controllers;

use app\Controllers\Controller;
use app\Models\AccountModel;
use app\Models\AuthModel;
use app\Models\ObjectiveModel;
use app\models\CategoryModel;
use app\Models\ObjectiveUserModel;
use app\Models\RoleModel;
use app\Models\TransactionModel;

require "../app/core/functions.php";
session_start();

class ObjectiveController
{
    private $objectiveModel;

    function __construct()
    {
        $this->objectiveModel = new ObjectiveModel();
    }

    // Views
    public function showUserObjectivesTable()
    {
        if (AuthModel::checkLogin()) {
            require "../app/core/translate.php";
            $_SESSION['path'] = $_SERVER['REQUEST_URI'];
            $_SESSION['page'] = "manage goals";

            $userId = $this->objectiveModel->id;
            $userCoin = $this->objectiveModel->coin;
            $userObjectives = $this->objectiveModel->getUserObjectives($userId);

            $data = [
                "translate" => $translate,
                "userObjectives" => $userObjectives,
                "userCoin" => $userCoin
            ];

            return Controller::view("objectives/manage-objectives", $data);
        }
    }
    public function showAddObjectiveForm()
    {
        if (AuthModel::checkLogin()) {
            require "../app/core/translate.php";
            $_SESSION['path'] = $_SERVER['REQUEST_URI'];
            $_SESSION['page'] = "create objective";

            $userCoin = $this->objectiveModel->coin;

            $data = [
                "translate" => $translate,
                "userCoin" => $userCoin
            ];

            return Controller::view("objectives/create-objective", $data);
        }
    }
    public function showEditObjectiveForm($params)
    {
        if (AuthModel::checkLogin()) {
            require "../app/core/translate.php";
            $_SESSION['path'] = $_SERVER['REQUEST_URI'];
            $_SESSION['page'] = "manage goals";

            $objectiveId = $params->id;
            $userId = $this->objectiveModel->id;
            $userCoin = $this->objectiveModel->coin;

            $objective = $this->objectiveModel->getObjectiveByUserPermission($objectiveId,  $userId, "edit_objective");


            $data = [
                "translate" => $translate,
                "objective" => $objective,
                "userCoin" => $userCoin
            ];

            Controller::view("objectives/edit-objective", $data);
        }
    }
    public function showInvestObjectiveForm()
    {
        if (AuthModel::checkLogin()) {
            require "../app/core/translate.php";
            $_SESSION['path'] = $_SERVER['REQUEST_URI'];
            $_SESSION['page'] = "investing";

            $accountInstance = new AccountModel();

            $userId = $this->objectiveModel->id;
            $userCoin = $this->objectiveModel->coin;

            $accountsUser = $accountInstance->getUserAccountsByPermission($userId, "add_transactions");
            $objectivesUser = $this->objectiveModel->getUserObjectivesByPermissionNotClaimed($userId, "invest_objective");

            $maxGoal = 0;
            if ($objectivesUser)
                $maxGoal = $objectivesUser[0]['objective_value'] - $objectivesUser[0]['amount_invested'];

            $data = [
                "translate" => $translate,
                "accountsUser" => $accountsUser,
                "maxGoal" => $maxGoal,
                "objectivesUser" => $objectivesUser,
                "userCoin" => $userCoin
            ];

            Controller::view("objectives/invest-objective", $data);
        }
    }
    public function viewUserObjectives()
    {
        if (AuthModel::checkLogin()) {
            require "../app/core/translate.php";
            $_SESSION['path'] = $_SERVER['REQUEST_URI'];
            $_SESSION['page'] = "goals";


            $userId = $this->objectiveModel->id;
            $userCoin = $this->objectiveModel->coin;
            $objectivesUser = $this->objectiveModel->getUserObjectivesNotClaimed($userId);

            $data = [
                "translate" => $translate,
                "objectivesUser" => $objectivesUser,
                "userCoin" => $userCoin
            ];

            Controller::view("objectives/objectives", $data);
        }
    }
    public function viewUserCompletedObjectives()
    {
        if (AuthModel::checkLogin()) {
            require "../app/core/translate.php";
            $_SESSION['path'] = $_SERVER['REQUEST_URI'];
            $_SESSION['page'] = "claimed goals";

            $userId = $this->objectiveModel->id;
            $userCoin = $this->objectiveModel->coin;

            $userObjectives = $this->objectiveModel->getUserObjectivesCompleted($userId);

            $data = [
                "translate" => $translate,
                "userCoin" => $userCoin,
                "userCompletedObjectives" => $userObjectives
            ];

            Controller::view("objectives/completed-objectives", $data);
        }
    }

    //Backend
    public function addObjective($params)
    {
        if (AuthModel::checkLogin()) {
            $userId = $this->objectiveModel->id;
            $objectiveName = $params->name;
            $objectiveValue = $params->value;

            $objectiveId = $this->objectiveModel->addObjective($objectiveName, $objectiveValue);
            if ($objectiveId) {
                $objectiveUserInstance = new ObjectiveUserModel();
                $roleInstance = new RoleModel();
                $roleId = $roleInstance->getRoleIdByName("creator");

                if ($objectiveUserInstance->addObjectiveUser($objectiveId, $userId, $roleId))
                    echo 1;
                else
                    echo 0;
            } else
                echo 0;
        }
    }
    function updateObjective($params)
    {
        if (AuthModel::checkLogin()) {
            $objectiveId = $params->id;

            parse_str(file_get_contents("php://input"),  $_PUT);

            $userId = $this->objectiveModel->id;

            $objectiveUserInstance = new ObjectiveUserModel();
            $userRole = $objectiveUserInstance->getUserRole($objectiveId, $userId);

            if (hasPerm($userRole, "edit_objective")) {
                $objectiveName = $_PUT["name"];
                $objectiveValue = $_PUT["value"];

                $objectiveAmountInvested = $this->objectiveModel->getObjectiveAmountIvested($objectiveId);

                if ($objectiveAmountInvested < $objectiveValue)
                    $this->objectiveModel->markObjectiveIncompleted($objectiveId);

                if ($this->objectiveModel->updateObjective($objectiveId, $objectiveName, $objectiveValue))
                    echo 1;
                else
                    echo 0;
            } else
                echo 0;
        }
    }
    public function markObjectiveCompleted($params)
    {
        if (AuthModel::checkLogin()) {
            $userId = $this->objectiveModel->id;
            $objectiveId = $params->id;

            $objectiveUserInstance = new ObjectiveUserModel();

            $userRole = $objectiveUserInstance->getUserRole($objectiveId, $userId);

            if (hasPerm($userRole, "claim_objective")) {
                if ($this->objectiveModel->markObjectiveCompleted($objectiveId))
                    echo 1;
                else echo 0;
            } else
                echo 0;
        }
    }
    public function investObjective($params)
    {
        $objectiveId = $params->objective;
        $valueToInvest = $params->value;

        if ($this->objectiveModel->investObjective($objectiveId, $valueToInvest)) {
            $categoryInstance = new CategoryModel();
            $transactionInstance = new TransactionModel();

            $category = $categoryInstance->getCategoryIdByCode("investment");

            $currentDate = date("Y-m-d");
            $userId = $this->objectiveModel->id;
            $accountId = $params->account;

            if ($transactionInstance->addTransaction(0, $userId, $category, 0, $valueToInvest, $accountId, $currentDate, "", "")) {
                header("location: view");
            } else
                header("location: invest");
        } else
            header("location: invest");
    }
    public function deleteObjective($params)
    {
        if (AuthModel::checkLogin()) {
            $objectiveId = $params->id;

            $objectiveUserInstance = new ObjectiveUserModel();

            $userId = $this->objectiveModel->id;

            $userRole = $objectiveUserInstance->getUserRole($objectiveId, $userId);

            if (hasPerm($userRole, "delete_account")) {
                $this->objectiveModel->deleteObjective($objectiveId);
                echo 1;
            } else
                echo 0;
        }
    }
}
