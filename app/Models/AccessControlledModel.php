<?php
/**
 * Created by PhpStorm.
 * User: HLi
 * Date: 12/7/16
 * Time: 4:20 PM
 */

namespace App\Models;

use App\Observers\AccessControlObserver;
use REM\Database\AccessControlledBuilder as AccessControlledBuilder;
use REM\Database\AccessControlledEloquentBuilder as AccessControlledEloquentBuilder;
use REM\AccessControl\ModelAccessControlMethods;
class AccessControlledModel extends \Illuminate\Database\Eloquent\Model
{
    use ModelAccessControlMethods;
    public function __construct($attributes = []){
        parent::__construct($attributes);
        $this->registerAccessControl();
    }
    
    /*
     * Makes sure to enable access control for the particular model instance if it is not already enabled.
     */
    function registerAccessControl(){
        static $accessControlRegistered = false;
        $className = "\\".get_class($this);
        if (!$accessControlRegistered){
            $accessControlRegistered = true;
            $className::observe(AccessControlObserver::class);
        }
        return;
    }


    /**
     * Get a new query builder instance for the connection.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    protected function newBaseQueryBuilder()
    {
        $conn = $this->getConnection();

        $grammar = $conn->getQueryGrammar();

        return new AccessControlledBuilder($conn, $grammar, $conn->getPostProcessor());
    }

    /**
     *
     */
    public function newEloquentBuilder($query)
    {
        return new AccessControlledEloquentBuilder($query);
    }

    /*
     * Determines the current columns fot the table that this model is associated with.
     * @return array of column names
     */
    public function getTableColumns() {
        return $this->getConnection()->getSchemaBuilder()->getColumnListing($this->getTable());
    }
    
    /*
     * Determines the current columns fot the table that this model is associated with.
     */
    public function checkColumnExists($columnName) {
        return $this->getConnection()->getSchemaBuilder()->hasColumn($this->getTable(), $columnName);
    }

    public function exists(){
        return (!empty($this) && !empty($this->id) && is_integer($this->id))?(true):(false);
    }

}