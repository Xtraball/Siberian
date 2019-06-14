<?php

// Migrating all `promotion` to `discount` & `promotion_customer` to `discount_customer`
$promotionTableIsRenamed = __get("migration_promotion_4-17-0");
if ($promotionTableIsRenamed !== "done") {
    $queries = [
        "INSERT INTO discount ('discount_id','value_id','title','picture','thumbnail','description','conditions','is_unique','end_at','force_validation','is_active','condition_type','condition_number_of_points','condition_period_number','condition_period_type','is_shared','owner','unlock_by','unlock_code','created_at','updated_at') SELECT 'discount_id','value_id','title','picture','thumbnail','description','conditions','is_unique','end_at','force_validation','is_active','condition_type','condition_number_of_points','condition_period_number','condition_period_type','is_shared','owner','unlock_by','unlock_code','created_at','updated_at' FROM promotion;",
        "INSERT INTO discount_customer ('discount_customer_id','discount_id','pos_id','customer_id','is_used','number_of_error','created_at','last_error') SELECT 'discount_customer_id','discount_id','pos_id','customer_id','is_used','number_of_error','created_at','last_error' FROM promotion_customer;"
    ];

    foreach ($queries as $query) {
        try {
            $this->query($query);
        } catch (\Exception $e) {
            // Silent!
        }
    }

    __set("migration_promotion_4-17-0", "done");
}

