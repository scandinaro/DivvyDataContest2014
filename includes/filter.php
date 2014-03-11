<?php
/**
 * Created by PhpStorm.
 * User: dominic-scandinaro
 * Date: 3/9/14
 * Time: 10:26 PM
 */

class filter {
    public static function generateWhere($gender, $age, $user_type) {
        $filters = '';
        $filtersNoWhere = '';
        //setup where statement
        if ($gender != '' || $age != '' || $user_type != ''){
            $filters = array();
            if (!empty($gender)){
                if ($gender == 'm'){
                    $filters[] = "a.gender='Male'";
                }
                elseif ($gender == 'f'){
                    $filters[] = "a.gender='Female'";
                }
            }
            if (!empty($age)){
                if ($age == 18){
                    $filters[] = "a.birth_year>=YEAR(DATE_SUB(NOW(),INTERVAL 18 YEAR))";
                }
                elseif ($age == 19){
                    $filters[] = "a.birth_year<=YEAR(DATE_SUB(NOW(),INTERVAL 19 YEAR))";
                    $filters[] = "a.birth_year>=YEAR(DATE_SUB(NOW(),INTERVAL 21 YEAR))";
                }
                elseif ($age == 22){
                    $filters[] = "a.birth_year<=YEAR(DATE_SUB(NOW(),INTERVAL 22 YEAR))";
                    $filters[] = "a.birth_year>=YEAR(DATE_SUB(NOW(),INTERVAL 30 YEAR))";
                }
                elseif ($age == 31){
                    $filters[] = "a.birth_year<=YEAR(DATE_SUB(NOW(),INTERVAL 31 YEAR))";
                    $filters[] = "a.birth_year>=YEAR(DATE_SUB(NOW(),INTERVAL 40 YEAR))";
                }
                elseif ($age == 41){
                    $filters[] = "a.birth_year<=YEAR(DATE_SUB(NOW(),INTERVAL 41 YEAR))";
                    $filters[] = "a.birth_year>=YEAR(DATE_SUB(NOW(),INTERVAL 50 YEAR))";
                }
                elseif ($age == 51){
                    $filters[] = "a.birth_year<=YEAR(DATE_SUB(NOW(),INTERVAL 51 YEAR))";
                }
            }
            if (!empty($user_type)){
                if ($user_type == 'customer'){
                    $filters[] = "a.user_type='Customer'";
                }
                elseif ($user_type == 'subscriber'){
                    $filters[] = "a.user_type='Subscriber'";
                }
            }

            $filters = "WHERE ".implode(" AND ", $filters);
            $filtersNoWhere = str_replace("WHERE", "AND", $filters);
        }

        return array('filters'=>$filters, 'filters_no_where'=>$filtersNoWhere);
    }
} 