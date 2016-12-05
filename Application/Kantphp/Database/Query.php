<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Kant\Database;

use Kant\Foundation\Component;

class Query extends Component implements QueryInterface {
    
    use QueryTrait;

    /**
     * @var array the columns being selected. For example, `['id', 'name']`.
     * This is used to construct the SELECT clause in a SQL statement. If not set, it means selecting all columns.
     * @see select()
     */
    public $select;

    /**
     * @var string additional option that should be appended to the 'SELECT' keyword. For example,
     * in MySQL, the option 'SQL_CALC_FOUND_ROWS' can be used.
     */
    public $selectOption;

    /**
     * @var boolean whether to select distinct rows of data only. If this is set true,
     * the SELECT clause would be changed to SELECT DISTINCT.
     */
    public $distinct;

    /**
     * @var array the table(s) to be selected from. For example, `['user', 'post']`.
     * This is used to construct the FROM clause in a SQL statement.
     * @see from()
     */
    public $from;

    /**
     * @var array how to group the query results. For example, `['company', 'department']`.
     * This is used to construct the GROUP BY clause in a SQL statement.
     */
    public $groupBy;

    /**
     * @var array how to join with other tables. Each array element represents the specification
     * of one join which has the following structure:
     *
     * ```php
     * [$joinType, $tableName, $joinCondition]
     * ```
     *
     * For example,
     *
     * ```php
     * [
     *     ['INNER JOIN', 'user', 'user.id = author_id'],
     *     ['LEFT JOIN', 'team', 'team.id = team_id'],
     * ]
     * ```
     */
    public $join;

    /**
     * @var string|array the condition to be applied in the GROUP BY clause.
     * It can be either a string or an array. Please refer to [[where()]] on how to specify the condition.
     */
    public $having;

    /**
     * @var array this is used to construct the UNION clause(s) in a SQL statement.
     * Each array element is an array of the following structure:
     *
     * - `query`: either a string or a [[Query]] object representing a query
     * - `all`: boolean, whether it should be `UNION ALL` or `UNION`
     */
    public $union;

    /**
     * @var array list of query parameter values indexed by parameter placeholders.
     * For example, `[':name' => 'Dan', ':age' => 31]`.
     */
    public $params = [];

}
