<?php $this->layout("master"); ?>
<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <div
        class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><?= $translate["create_new_account"]; ?></h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" id="expense"
                    class="btn btn-sm btn-outline-danger"><?= $translate["add_expense"]; ?></button>
                <button type="button" id="revenue"
                    class="btn btn-sm btn-outline-success"><?= $translate["add_revenue"]; ?></button>
            </div>

        </div>
    </div>

    <div class="container-fluid px-1 py-5 mx-auto">
        <div class="row d-flex justify-content-center">
            <div class="col-xl-7 col-lg-8 col-md-9 col-11 text-center">
                <form class="form-card" action="" method="POST">
                    <div class="row justify-content-between text-left">
                        <div class="form-group col-sm-6 flex-column d-flex"> <label
                                class="form-control-label px-3"><?= $translate["name"]; ?><span
                                    class="text-danger-emphasis">
                                    *</span></label> <input type="name" class="input" name="name"
                                placeholder="<?= $translate["account"]; ?>..." required> </div>
                        <div class="form-group col-sm-6 flex-column d-flex"> <label
                                class="form-control-label px-3"><?= $translate["initial_cash"]; ?><span
                                    class="text-danger-emphasis"> *</span></label> <input type="number" class="input"
                                step="0.01" name="value" placeholder="0.00<?= $userCoin; ?>" min="0.00" required>
                        </div>
                    </div>
                    <div class="row justify-content-end">
                        <div class="form-group col-sm-6"> <button type="submit"
                                class="button btn btn-sm btn-outline-primary"><?= $translate["create_account"]; ?></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>