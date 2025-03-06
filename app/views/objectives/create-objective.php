 <?php
    $this->layout("master");
    $_SESSION['path'] = $_SERVER['REQUEST_URI'];
    $_SESSION['page'] = "create objective";
    if (!isset($_COOKIE['user']))
        header("location: /Cash_manager/public/sing-up");
    require_once("../backend/querys.php");
    require_once "../backend/language.php";

    ?>

 <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
     <div
         class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
         <h1 class="h2"><?php echo $create_new_objective; ?></h1>
         <div class="btn-toolbar mb-2 mb-md-0">
             <div class="btn-group me-2">
                 <button type="button" id="expense" class="btn btn-sm btn-outline-danger"><?php echo $add_expense; ?></button>
                 <button type="button" id="revenue" class="btn btn-sm btn-outline-success"><?Php echo $add_revenue; ?></button>
             </div>

         </div>
     </div>

     <div class="container-fluid px-1 py-5 mx-auto">
         <div class="row d-flex justify-content-center">
             <div class="col-xl-7 col-lg-8 col-md-9 col-11 text-center">
                 <form class="form-card" action="" method="POST">
                     <div class="row justify-content-between text-left">
                         <div class="form-group col-sm-6 flex-column d-flex"> <label class="form-control-label px-3"><?php echo $name; ?><span class="text-danger-emphasis"> *</span></label> <input type="name" class="input" id="fname" name="name" placeholder="<?= $objective; ?>" required> </div>
                         <div class="form-group col-sm-6 flex-column d-flex"> <label class="form-control-label px-3"><?Php echo $objective_value; ?><span class="text-danger-emphasis"> *</span></label> <input type="number" class="input" step="0.01" id="fname" name="value" min="0.01" placeholder="0.00<?= $coin; ?>" required> </div>
                     </div>
                     <div class="row justify-content-end">
                         <div class="form-group col-sm-6"> <button type="submit" class="button btn btn-sm btn-outline-primary"><?Php echo $create_objective; ?></button> </div>
                     </div>
                 </form>
             </div>
         </div>
     </div>
 </main>