<?php

/*
 * This file is part of composer/semver.
 *
 * (c) Composer <https://github.com/composer>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Composer\Semver\Constraint;

use PHPUnit\Framework\TestCase;

class ConstraintTest extends TestCase
{
    /**
     * @var Constraint
     */
    protected $constraint;
    /**
     * @var Constraint
     */
    protected $versionProvide;

    protected function setUp()
    {
        $this->constraint = new Constraint('==', '1');
        $this->versionProvide = new Constraint('==', 'dev-foo');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testVersionCompareInvalidArgumentException()
    {
        $result = $this->constraint->versionCompare('1.1', '1.2', '!==');
    }

    public function testGetPrettyString()
    {
        $expectedString = 'pretty-string';
        $this->constraint->setPrettyString($expectedString);
        $result = $this->constraint->getPrettyString();

        $this->assertSame($expectedString, $result);

        $expectedVersion = '== 1';
        $this->constraint->setPrettyString(null);
        $result = $this->constraint->getPrettyString();

        $this->assertSame($expectedVersion, $result);
    }

    public static function successfulVersionMatches()
    {
        return array(
            //    require    provide
            array('==', '2', '==', '2'),
            array('==', '2', '<', '3'),
            array('==', '2', '<=', '2'),
            array('==', '2', '<=', '3'),
            array('==', '2', '>=', '1'),
            array('==', '2', '>=', '2'),
            array('==', '2', '>', '1'),
            array('==', '2', '!=', '1'),
            array('==', '2', '!=', '3'),

            array('<', '2', '==', '1'),
            array('<', '2', '<', '1'),
            array('<', '2', '<', '2'),
            array('<', '2', '<', '3'),
            array('<', '2', '<=', '1'),
            array('<', '2', '<=', '2'),
            array('<', '2', '<=', '3'),
            array('<', '2', '>=', '1'),
            array('<', '2', '>', '1'),
            array('<', '2', '!=', '1'),
            array('<', '2', '!=', '2'),
            array('<', '2', '!=', '3'),

            array('<=', '2', '==', '1'),
            array('<=', '2', '==', '2'),
            array('<=', '2', '<', '1'),
            array('<=', '2', '<', '2'),
            array('<=', '2', '<', '3'),
            array('<=', '2', '<=', '1'),
            array('<=', '2', '<=', '2'),
            array('<=', '2', '<=', '3'),
            array('<=', '2', '>=', '1'),
            array('<=', '2', '>=', '2'),
            array('<=', '2', '>', '1'),
            array('<=', '2', '!=', '1'),
            array('<=', '2', '!=', '2'),
            array('<=', '2', '!=', '3'),

            array('>=', '2', '==', '2'),
            array('>=', '2', '==', '3'),
            array('>=', '2', '<', '3'),
            array('>=', '2', '<=', '2'),
            array('>=', '2', '<=', '3'),
            array('>=', '2', '>=', '1'),
            array('>=', '2', '>=', '2'),
            array('>=', '2', '>=', '3'),
            array('>=', '2', '>', '1'),
            array('>=', '2', '>', '2'),
            array('>=', '2', '>', '3'),
            array('>=', '2', '!=', '1'),
            array('>=', '2', '!=', '2'),
            array('>=', '2', '!=', '3'),

            array('>', '2', '==', '3'),
            array('>', '2', '<', '3'),
            array('>', '2', '<=', '3'),
            array('>', '2', '>=', '1'),
            array('>', '2', '>=', '2'),
            array('>', '2', '>=', '3'),
            array('>', '2', '>', '1'),
            array('>', '2', '>', '2'),
            array('>', '2', '>', '3'),
            array('>', '2', '!=', '1'),
            array('>', '2', '!=', '2'),
            array('>', '2', '!=', '3'),

            array('!=', '2', '!=', '1'),
            array('!=', '2', '!=', '2'),
            array('!=', '2', '!=', '3'),
            array('!=', '2', '==', '1'),
            array('!=', '2', '==', '3'),
            array('!=', '2', '<', '1'),
            array('!=', '2', '<', '2'),
            array('!=', '2', '<', '3'),
            array('!=', '2', '<=', '1'),
            array('!=', '2', '<=', '2'),
            array('!=', '2', '<=', '3'),
            array('!=', '2', '>=', '1'),
            array('!=', '2', '>=', '2'),
            array('!=', '2', '>=', '3'),
            array('!=', '2', '>', '1'),
            array('!=', '2', '>', '2'),
            array('!=', '2', '>', '3'),

            // branch names
            array('==', 'dev-foo-bar', '==', 'dev-foo-bar'),
            array('==', 'dev-events+issue-17', '==', 'dev-events+issue-17'),
            array('==', 'dev-foo-bar', '!=', 'dev-foo-xyz'),

            array('!=', 'dev-foo-bar', '!=', 'dev-foo-xyz'),

            // numbers vs branches
            array('==', '0.12', '!=', 'dev-foo'),
            array('<', '0.12', '!=', 'dev-foo'),
            array('<=', '0.12', '!=', 'dev-foo'),
            array('>=', '0.12', '!=', 'dev-foo'),
            array('>', '0.12', '!=', 'dev-foo'),
            array('!=', '0.12', '==', 'dev-foo'),
            array('!=', '0.12', '!=', 'dev-foo'),
        );
    }

    /**
     * @dataProvider successfulVersionMatches
     */
    public function testVersionMatchSucceeds($requireOperator, $requireVersion, $provideOperator, $provideVersion)
    {
        $versionRequire = new Constraint($requireOperator, $requireVersion);
        $versionProvide = new Constraint($provideOperator, $provideVersion);

        $this->assertTrue($versionRequire->matches($versionProvide));
        $this->assertTrue($this->matchCompiled($versionRequire, $provideOperator, $provideVersion));
        // the operation should be commutative
        $this->assertTrue($versionProvide->matches($versionRequire));
        $this->assertTrue($this->matchCompiled($versionProvide, $requireOperator, $requireVersion));
    }

    public static function failingVersionMatches()
    {
        return array(
            //    require    provide
            array('==', '2', '==', '1'),
            array('==', '2', '==', '3'),
            array('==', '2', '<', '1'),
            array('==', '2', '<', '2'),
            array('==', '2', '<=', '1'),
            array('==', '2', '>=', '3'),
            array('==', '2', '>', '2'),
            array('==', '2', '>', '3'),
            array('==', '2', '!=', '2'),

            array('<', '2', '==', '2'),
            array('<', '2', '==', '3'),
            array('<', '2', '>=', '2'),
            array('<', '2', '>=', '3'),
            array('<', '2', '>', '2'),
            array('<', '2', '>', '3'),

            array('<=', '2', '==', '3'),
            array('<=', '2', '>=', '3'),
            array('<=', '2', '>', '2'),
            array('<=', '2', '>', '3'),

            array('>=', '2', '==', '1'),
            array('>=', '2', '<', '1'),
            array('>=', '2', '<', '2'),
            array('>=', '2', '<=', '1'),

            array('>', '2', '==', '1'),
            array('>', '2', '==', '2'),
            array('>', '2', '<', '1'),
            array('>', '2', '<', '2'),
            array('>', '2', '<=', '1'),
            array('>', '2', '<=', '2'),

            array('!=', '2', '==', '2'),

            array('==', '2.0-b2', '<', '2.0-beta2'),
            array('==', 'dev-foo-dist', '==', 'dev-foo-zist'),

            // different branch names
            array('==', 'dev-foo-bar', '==', 'dev-foo-xyz'),
            array('==', 'dev-foo-bar', '<', 'dev-foo-xyz'),
            array('==', 'dev-foo-bar', '<=', 'dev-foo-xyz'),
            array('==', 'dev-foo-bar', '>=', 'dev-foo-xyz'),
            array('==', 'dev-foo-bar', '>', 'dev-foo-xyz'),

            array('<', 'dev-foo-bar', '==', 'dev-foo-xyz'),
            array('<', 'dev-foo-bar', '<', 'dev-foo-xyz'),
            array('<', 'dev-foo-bar', '<=', 'dev-foo-xyz'),
            array('<', 'dev-foo-bar', '>=', 'dev-foo-xyz'),
            array('<', 'dev-foo-bar', '>', 'dev-foo-xyz'),
            array('<', 'dev-foo-bar', '!=', 'dev-foo-xyz'),

            array('<=', 'dev-foo-bar', '==', 'dev-foo-xyz'),
            array('<=', 'dev-foo-bar', '<', 'dev-foo-xyz'),
            array('<=', 'dev-foo-bar', '<=', 'dev-foo-xyz'),
            array('<=', 'dev-foo-bar', '>=', 'dev-foo-xyz'),
            array('<=', 'dev-foo-bar', '>', 'dev-foo-xyz'),
            array('<=', 'dev-foo-bar', '!=', 'dev-foo-xyz'),

            array('>=', 'dev-foo-bar', '==', 'dev-foo-xyz'),
            array('>=', 'dev-foo-bar', '<', 'dev-foo-xyz'),
            array('>=', 'dev-foo-bar', '<=', 'dev-foo-xyz'),
            array('>=', 'dev-foo-bar', '>=', 'dev-foo-xyz'),
            array('>=', 'dev-foo-bar', '>', 'dev-foo-xyz'),
            array('>=', 'dev-foo-bar', '!=', 'dev-foo-xyz'),

            array('>', 'dev-foo-bar', '==', 'dev-foo-xyz'),
            array('>', 'dev-foo-bar', '<', 'dev-foo-xyz'),
            array('>', 'dev-foo-bar', '<=', 'dev-foo-xyz'),
            array('>', 'dev-foo-bar', '>=', 'dev-foo-xyz'),
            array('>', 'dev-foo-bar', '>', 'dev-foo-xyz'),
            array('>', 'dev-foo-bar', '!=', 'dev-foo-xyz'),

            // same branch names
            array('==', 'dev-foo-bar', '<', 'dev-foo-bar'),
            array('==', 'dev-foo-bar', '<=', 'dev-foo-bar'),
            array('==', 'dev-foo-bar', '>=', 'dev-foo-bar'),
            array('==', 'dev-foo-bar', '>', 'dev-foo-bar'),
            array('==', 'dev-foo-bar', '!=', 'dev-foo-bar'),

            array('<', 'dev-foo-bar', '==', 'dev-foo-bar'),
            array('<', 'dev-foo-bar', '<', 'dev-foo-bar'),
            array('<', 'dev-foo-bar', '<=', 'dev-foo-bar'),
            array('<', 'dev-foo-bar', '>=', 'dev-foo-bar'),
            array('<', 'dev-foo-bar', '>', 'dev-foo-bar'),
            array('<', 'dev-foo-bar', '!=', 'dev-foo-bar'),

            array('<=', 'dev-foo-bar', '==', 'dev-foo-bar'),
            array('<=', 'dev-foo-bar', '<', 'dev-foo-bar'),
            array('<=', 'dev-foo-bar', '<=', 'dev-foo-bar'),
            array('<=', 'dev-foo-bar', '>=', 'dev-foo-bar'),
            array('<=', 'dev-foo-bar', '>', 'dev-foo-bar'),
            array('<=', 'dev-foo-bar', '!=', 'dev-foo-bar'),

            array('>=', 'dev-foo-bar', '==', 'dev-foo-bar'),
            array('>=', 'dev-foo-bar', '<', 'dev-foo-bar'),
            array('>=', 'dev-foo-bar', '<=', 'dev-foo-bar'),
            array('>=', 'dev-foo-bar', '>=', 'dev-foo-bar'),
            array('>=', 'dev-foo-bar', '>', 'dev-foo-bar'),
            array('>=', 'dev-foo-bar', '!=', 'dev-foo-bar'),

            array('>', 'dev-foo-bar', '==', 'dev-foo-bar'),
            array('>', 'dev-foo-bar', '<', 'dev-foo-bar'),
            array('>', 'dev-foo-bar', '<=', 'dev-foo-bar'),
            array('>', 'dev-foo-bar', '>=', 'dev-foo-bar'),
            array('>', 'dev-foo-bar', '>', 'dev-foo-bar'),
            array('>', 'dev-foo-bar', '!=', 'dev-foo-bar'),

            // branch vs number, not comparable so mostly false
            array('==', '0.12', '==', 'dev-foo'),
            array('==', '0.12', '<', 'dev-foo'),
            array('==', '0.12', '<=', 'dev-foo'),
            array('==', '0.12', '>=', 'dev-foo'),
            array('==', '0.12', '>', 'dev-foo'),

            array('<', '0.12', '==', 'dev-foo'),
            array('<', '0.12', '<', 'dev-foo'),
            array('<', '0.12', '<=', 'dev-foo'),
            array('<', '0.12', '>=', 'dev-foo'),
            array('<', '0.12', '>', 'dev-foo'),

            array('<=', '0.12', '==', 'dev-foo'),
            array('<=', '0.12', '<', 'dev-foo'),
            array('<=', '0.12', '<=', 'dev-foo'),
            array('<=', '0.12', '>=', 'dev-foo'),
            array('<=', '0.12', '>', 'dev-foo'),

            array('>=', '0.12', '==', 'dev-foo'),
            array('>=', '0.12', '<', 'dev-foo'),
            array('>=', '0.12', '<=', 'dev-foo'),
            array('>=', '0.12', '>=', 'dev-foo'),
            array('>=', '0.12', '>', 'dev-foo'),

            array('>', '0.12', '==', 'dev-foo'),
            array('>', '0.12', '<', 'dev-foo'),
            array('>', '0.12', '<=', 'dev-foo'),
            array('>', '0.12', '>=', 'dev-foo'),
            array('>', '0.12', '>', 'dev-foo'),

            array('!=', '0.12', '<', 'dev-foo'),
            array('!=', '0.12', '<=', 'dev-foo'),
            array('!=', '0.12', '>=', 'dev-foo'),
            array('!=', '0.12', '>', 'dev-foo'),
        );
    }

    /**
     * @dataProvider failingVersionMatches
     */
    public function testVersionMatchFails($requireOperator, $requireVersion, $provideOperator, $provideVersion)
    {
        $versionRequire = new Constraint($requireOperator, $requireVersion);
        $versionProvide = new Constraint($provideOperator, $provideVersion);

        $this->assertFalse($versionRequire->matches($versionProvide));
        $this->assertFalse($this->matchCompiled($versionRequire, $provideOperator, $provideVersion));
        // the operation should be commutative
        $this->assertFalse($versionProvide->matches($versionRequire));
        $this->assertFalse($this->matchCompiled($versionProvide, $requireOperator, $requireVersion));
    }

    public function testInverseMatchingOtherConstraints()
    {
        $constraint = new Constraint('>', '1.0.0');

        $multiConstraint = $this
            ->getMockBuilder('Composer\Semver\Constraint\MultiConstraint')
            ->disableOriginalConstructor()
            ->setMethods(array('matches'))
            ->getMock()
        ;

        $emptyConstraint = $this
            ->getMockBuilder('Composer\Semver\Constraint\EmptyConstraint')
            ->setMethods(array('matches'))
            ->getMock()
        ;

        foreach (array($multiConstraint, $emptyConstraint) as $mock) {
            $mock
                ->expects($this->once())
                ->method('matches')
                ->with($constraint)
                ->willReturn(true)
            ;
        }

        // @phpstan-ignore-next-line
        $this->assertTrue($constraint->matches($multiConstraint));
        // @phpstan-ignore-next-line
        $this->assertTrue($constraint->matches($emptyConstraint));
    }

    public function testComparableBranches()
    {
        $versionRequire = new Constraint('>', '0.12');

        $this->assertFalse($versionRequire->matches($this->versionProvide));
        $this->assertFalse($this->matchCompiled($versionRequire, '==', 'dev-foo'));
        $this->assertFalse($versionRequire->matchSpecific($this->versionProvide, true));

        $versionRequire = new Constraint('<', '0.12');

        $this->assertFalse($versionRequire->matches($this->versionProvide));
        $this->assertFalse($this->matchCompiled($versionRequire, '==', 'dev-foo'));
        $this->assertTrue($versionRequire->matchSpecific($this->versionProvide, true));
    }

    /**
     * @dataProvider invalidOperators
     *
     * @param string $version
     * @param string $operator
     * @param bool   $expected
     */
    public function testInvalidOperators($version, $operator, $expected)
    {
        if (method_exists($this, 'expectException')) {
            // @phpstan-ignore-next-line
            $this->expectException($expected);
        } else {
            // @phpstan-ignore-next-line
            $this->setExpectedException($expected);
        }

        new Constraint($operator, $version);
    }

    /**
     * @return array
     */
    public function invalidOperators()
    {
        return array(
            array('1.2.3', 'invalid', 'InvalidArgumentException'),
            array('1.2.3', '!', 'InvalidArgumentException'),
            array('1.2.3', 'equals', 'InvalidArgumentException'),
        );
    }

    /**
     * @dataProvider bounds
     *
     * @param string $operator
     * @param string $normalizedVersion
     * @param Bound  $expectedLower
     * @param Bound  $expectedUpper
     */
    public function testBounds($operator, $normalizedVersion, Bound $expectedLower, Bound $expectedUpper)
    {
        $constraint = new Constraint($operator, $normalizedVersion);

        $this->assertEquals($expectedLower, $constraint->getLowerBound(), 'Expected lower bound does not match');
        $this->assertEquals($expectedUpper, $constraint->getUpperBound(), 'Expected upper bound does not match');
    }

    /**
     * @return array
     */
    public function bounds()
    {
        return array(
            'equal to 1.0.0.0' => array('==', '1.0.0.0', new Bound('1.0.0.0', true), new Bound('1.0.0.0', true)),
            'equal to 1.0.0.0-rc3' => array('==', '1.0.0.0-rc3', new Bound('1.0.0.0-rc3', true), new Bound('1.0.0.0-rc3', true)),
            'equal to dev-feature-branch' => array('>=', 'dev-feature-branch', Bound::zero(), Bound::positiveInfinity()),

            'lower than 0.0.4.0' => array('<', '0.0.4.0', Bound::zero(), new Bound('0.0.4.0', false)),
            'lower than 1.0.0.0' => array('<', '1.0.0.0', Bound::zero(), new Bound('1.0.0.0', false)),
            'lower than 2.0.0.0' => array('<', '2.0.0.0', Bound::zero(), new Bound('2.0.0.0', false)),
            'lower than 3.0.3.0' => array('<', '3.0.3.0', Bound::zero(), new Bound('3.0.3.0', false)),
            'lower than 3.0.3.0-rc3' => array('<', '3.0.3.0-rc3', Bound::zero(), new Bound('3.0.3.0-rc3', false)),
            'lower than dev-feature-branch' => array('<', 'dev-feature-branch', Bound::zero(), Bound::positiveInfinity()),

            'greater than 0.0.4.0' => array('>', '0.0.4.0', new Bound('0.0.4.0', false), Bound::positiveInfinity()),
            'greater than 1.0.0.0' => array('>', '1.0.0.0', new Bound('1.0.0.0', false), Bound::positiveInfinity()),
            'greater than 2.0.0.0' => array('>', '2.0.0.0', new Bound('2.0.0.0', false), Bound::positiveInfinity()),
            'greater than 3.0.3.0' => array('>', '3.0.3.0', new Bound('3.0.3.0', false), Bound::positiveInfinity()),
            'greater than 3.0.3.0-rc3' => array('>', '3.0.3.0-rc3', new Bound('3.0.3.0-rc3', false), Bound::positiveInfinity()),
            'greater than dev-feature-branch' => array('>', 'dev-feature-branch', Bound::zero(), Bound::positiveInfinity()),

            'lower than or equal to 0.0.4.0' => array('<=', '0.0.4.0', Bound::zero(), new Bound('0.0.4.0', true)),
            'lower than or equal to 1.0.0.0' => array('<=', '1.0.0.0', Bound::zero(), new Bound('1.0.0.0', true)),
            'lower than or equal to 2.0.0.0' => array('<=', '2.0.0.0', Bound::zero(), new Bound('2.0.0.0', true)),
            'lower than or equal to 3.0.3.0' => array('<=', '3.0.3.0', Bound::zero(), new Bound('3.0.3.0', true)),
            'lower than or equal to 3.0.3.0-rc3' => array('<=', '3.0.3.0-rc3', Bound::zero(), new Bound('3.0.3.0-rc3', true)),
            'lower than or equal to dev-feature-branch' => array('<=', 'dev-feature-branch', Bound::zero(), Bound::positiveInfinity()),

            'greater than or equal to 0.0.4.0' => array('>=', '0.0.4.0', new Bound('0.0.4.0', true), Bound::positiveInfinity()),
            'greater than or equal to 1.0.0.0' => array('>=', '1.0.0.0', new Bound('1.0.0.0', true), Bound::positiveInfinity()),
            'greater than or equal to 2.0.0.0' => array('>=', '2.0.0.0', new Bound('2.0.0.0', true), Bound::positiveInfinity()),
            'greater than or equal to 3.0.3.0' => array('>=', '3.0.3.0', new Bound('3.0.3.0', true), Bound::positiveInfinity()),
            'greater than or equal to 3.0.3.0-rc3' => array('>=', '3.0.3.0-rc3', new Bound('3.0.3.0-rc3', true), Bound::positiveInfinity()),
            'greater than or equal to dev-feature-branch' => array('>=', 'dev-feature-branch', Bound::zero(), Bound::positiveInfinity()),

            'not equal to 1.0.0.0' => array('<>', '1.0.0.0', Bound::zero(), Bound::positiveInfinity()),
        );
    }

    /**
     * @dataProvider matrix
     */
    public function testCompile($requireOperator, $requireVersion, $provideOperator, $provideVersion)
    {
        $require = new Constraint($requireOperator, $requireVersion);
        $provide = new Constraint($provideOperator, $provideVersion);

        // Asserts Compiled version returns the same result than standard
        $this->assertSame($require->matches($provide), $this->matchCompiled($require, $provideOperator, $provideVersion));
    }

    public function matrix()
    {
        $versions = array('1.0', '2.0', 'dev-master', 'dev-foo', '3.0-b2', '3.0-beta2');
        $operators = array('==', '!=', '>', '<', '>=', '<=');

        $matrix = array();
        foreach ($versions as $requireVersion) {
            foreach ($operators as $requireOperator) {
                foreach ($versions as $provideVersion) {
                    foreach ($operators as $provideOperator) {
                        $matrix[] = array($requireOperator, $requireVersion, $provideOperator, $provideVersion);
                    }
                }
            }
        }

        return $matrix;
    }

    private function matchCompiled(CompilableConstraintInterface $constraint, $operator, $version)
    {
        $map = array(
            '=' => Constraint::OP_EQ,
            '==' => Constraint::OP_EQ,
            '<' => Constraint::OP_LT,
            '<=' => Constraint::OP_LE,
            '>' => Constraint::OP_GT,
            '>=' => Constraint::OP_GE,
            '<>' => Constraint::OP_NE,
            '!=' => Constraint::OP_NE,
        );

        $code = $constraint->compile($map[$operator]);
        $v = $version;
        $b = 'dev-' === substr($v, 0, 4);

        return eval("return $code;");
    }
}
