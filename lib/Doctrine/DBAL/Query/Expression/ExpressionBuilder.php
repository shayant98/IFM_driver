<?php

declare(strict_types=1);

namespace Doctrine\DBAL\Query\Expression;

use Doctrine\DBAL\Connection;
use function implode;
use function sprintf;

/**
 * ExpressionBuilder class is responsible to dynamically create SQL query parts.
 */
class ExpressionBuilder
{
    public const EQ  = '=';
    public const NEQ = '<>';
    public const LT  = '<';
    public const LTE = '<=';
    public const GT  = '>';
    public const GTE = '>=';

    /**
     * The DBAL Connection.
     *
     * @var Connection
     */
    private $connection;

    /**
     * Initializes a new <tt>ExpressionBuilder</tt>.
     *
     * @param Connection $connection The DBAL Connection.
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Creates a conjunction of the given expressions.
     *
     * @param string|CompositeExpression $expression
     * @param string|CompositeExpression ...$expressions
     */
    public function and($expression, ...$expressions) : CompositeExpression
    {
        return CompositeExpression::and($expression, ...$expressions);
    }

    /**
     * Creates a disjunction of the given expressions.
     *
     * @param string|CompositeExpression $expression
     * @param string|CompositeExpression ...$expressions
     */
    public function or($expression, ...$expressions) : CompositeExpression
    {
        return CompositeExpression::or($expression, ...$expressions);
    }

    /**
     * Creates a comparison expression.
     *
     * @param string $x        The left expression.
     * @param string $operator The comparison operator.
     * @param string $y        The right expression.
     */
    public function comparison(string $x, string $operator, string $y) : string
    {
        return $x . ' ' . $operator . ' ' . $y;
    }

    /**
     * Creates an equality comparison expression with the given arguments.
     *
     * First argument is considered the left expression and the second is the right expression.
     * When converted to string, it will generated a <left expr> = <right expr>. Example:
     *
     *     [php]
     *     // u.id = ?
     *     $expr->eq('u.id', '?');
     *
     * @param string $x The left expression.
     * @param string $y The right expression.
     */
    public function eq(string $x, string $y) : string
    {
        return $this->comparison($x, self::EQ, $y);
    }

    /**
     * Creates a non equality comparison expression with the given arguments.
     * First argument is considered the left expression and the second is the right expression.
     * When converted to string, it will generated a <left expr> <> <right expr>. Example:
     *
     *     [php]
     *     // u.id <> 1
     *     $q->where($q->expr()->neq('u.id', '1'));
     *
     * @param string $x The left expression.
     * @param string $y The right expression.
     */
    public function neq(string $x, string $y) : string
    {
        return $this->comparison($x, self::NEQ, $y);
    }

    /**
     * Creates a lower-than comparison expression with the given arguments.
     * First argument is considered the left expression and the second is the right expression.
     * When converted to string, it will generated a <left expr> < <right expr>. Example:
     *
     *     [php]
     *     // u.id < ?
     *     $q->where($q->expr()->lt('u.id', '?'));
     *
     * @param string $x The left expression.
     * @param string $y The right expression.
     */
    public function lt(string $x, string $y) : string
    {
        return $this->comparison($x, self::LT, $y);
    }

    /**
     * Creates a lower-than-equal comparison expression with the given arguments.
     * First argument is considered the left expression and the second is the right expression.
     * When converted to string, it will generated a <left expr> <= <right expr>. Example:
     *
     *     [php]
     *     // u.id <= ?
     *     $q->where($q->expr()->lte('u.id', '?'));
     *
     * @param string $x The left expression.
     * @param string $y The right expression.
     */
    public function lte(string $x, string $y) : string
    {
        return $this->comparison($x, self::LTE, $y);
    }

    /**
     * Creates a greater-than comparison expression with the given arguments.
     * First argument is considered the left expression and the second is the right expression.
     * When converted to string, it will generated a <left expr> > <right expr>. Example:
     *
     *     [php]
     *     // u.id > ?
     *     $q->where($q->expr()->gt('u.id', '?'));
     *
     * @param string $x The left expression.
     * @param string $y The right expression.
     */
    public function gt(string $x, string $y) : string
    {
        return $this->comparison($x, self::GT, $y);
    }

    /**
     * Creates a greater-than-equal comparison expression with the given arguments.
     * First argument is considered the left expression and the second is the right expression.
     * When converted to string, it will generated a <left expr> >= <right expr>. Example:
     *
     *     [php]
     *     // u.id >= ?
     *     $q->where($q->expr()->gte('u.id', '?'));
     *
     * @param string $x The left expression.
     * @param string $y The right expression.
     */
    public function gte(string $x, string $y) : string
    {
        return $this->comparison($x, self::GTE, $y);
    }

    /**
     * Creates an IS NULL expression with the given arguments.
     *
     * @param string $x The field in string format to be restricted by IS NULL.
     */
    public function isNull(string $x) : string
    {
        return $x . ' IS NULL';
    }

    /**
     * Creates an IS NOT NULL expression with the given arguments.
     *
     * @param string $x The field in string format to be restricted by IS NOT NULL.
     */
    public function isNotNull(string $x) : string
    {
        return $x . ' IS NOT NULL';
    }

    /**
     * Creates a LIKE comparison expression.
     *
     * @param string $expression The expression to be inspected by the LIKE comparison
     * @param string $pattern    The pattern to compare against
     */
    public function like(string $expression, string $pattern, ?string $escapeChar = null) : string
    {
        return $this->comparison($expression, 'LIKE', $pattern) .
            ($escapeChar !== null ? sprintf(' ESCAPE %s', $escapeChar) : '');
    }

    /**
     * Creates a NOT LIKE comparison expression
     *
     * @param string $expression The expression to be inspected by the NOT LIKE comparison
     * @param string $pattern    The pattern to compare against
     */
    public function notLike(string $expression, string $pattern, ?string $escapeChar = null) : string
    {
        return $this->comparison($expression, 'NOT LIKE', $pattern) .
            ($escapeChar !== null ? sprintf(' ESCAPE %s', $escapeChar) : '');
    }

    /**
     * Creates a IN () comparison expression with the given arguments.
     *
     * @param string          $x The field in string format to be inspected by IN() comparison.
     * @param string|string[] $y The placeholder or the array of values to be used by IN() comparison.
     */
    public function in(string $x, $y) : string
    {
        return $this->comparison($x, 'IN', '(' . implode(', ', (array) $y) . ')');
    }

    /**
     * Creates a NOT IN () comparison expression with the given arguments.
     *
     * @param string          $x The field in string format to be inspected by NOT IN() comparison.
     * @param string|string[] $y The placeholder or the array of values to be used by NOT IN() comparison.
     */
    public function notIn(string $x, $y) : string
    {
        return $this->comparison($x, 'NOT IN', '(' . implode(', ', (array) $y) . ')');
    }

    /**
     * Creates an SQL literal expression from the string.
     */
    public function literal(string $input) : string
    {
        return $this->connection->quote($input);
    }
}
