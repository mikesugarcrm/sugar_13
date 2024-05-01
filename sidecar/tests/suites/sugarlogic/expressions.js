/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
describe('SugarCRM expressions logic', function() {
    describe('Expression', function() {
        describe('isTruthy', function() {
            it('should be truthy if given the string "1" or TRUE', function() {
                expect(SUGAR.expressions.Expression.isTruthy('1')).toBe(true);
                expect(SUGAR.expressions.Expression.isTruthy(SUGAR.expressions.Expression.TRUE)).toBe(true);
            });

            it('should be falsy for all other strings', function() {
                expect(SUGAR.expressions.Expression.isTruthy('random string')).toBe(false);
            });

            it('should handle boolean values', function() {
                expect(SUGAR.expressions.Expression.isTruthy(true)).toBe(true);
                expect(SUGAR.expressions.Expression.isTruthy(false)).toBe(false);
            });

            it('should handle numeric values', function() {
                expect(SUGAR.expressions.Expression.isTruthy(5)).toBe(true);
                expect(SUGAR.expressions.Expression.isTruthy(0)).toBe(false);
            });
        });

        describe('isNumeric', function() {
            it('should return false except for numbers and strings', function() {
                expect(SUGAR.expressions.isNumeric({iam: 'anObject'})).toBe(false);
                expect(SUGAR.expressions.isNumeric(false)).toBe(false);
            });

            it('should return true for finite numbers', function() {
                expect(SUGAR.expressions.isNumeric(5)).toBe(true);
            });

            it('should return true for numeric strings', function() {
                expect(SUGAR.expressions.isNumeric('20')).toBe(true);
            });

            it('should return false for NaN', function() {
                expect(SUGAR.expressions.isNumeric(NaN)).toBe(false);
            });

            it('should return false for non-numeric strings', function() {
                expect(SUGAR.expressions.isNumeric('definitely not a number')).toBe(false);
            });
        });

        describe('replaceAll', function() {
            it('should replace a selected string multiple times', function() {
                let haystack = 'repeated substrings in a repeated string of repeated repetitions';
                let result = SUGAR.expressions.replaceAll(haystack, 'repeated', 'duplicate');
                expect(result).toBe('duplicate substrings in a duplicate string of duplicate repetitions');
            });
        });
    });

    describe('Expression Parser', function() {
        beforeEach(function() {
            this.parser = new SUGAR.expressions.ExpressionParser();
        });

        describe('evaluate', function() {
            it('should only parse strings', function() {
                expect(() => this.parser.evaluate(5)).toThrow('ExpressionParser requires a string expression.');
            });

            it('should return a constant if given a constant expression', function() {
                let result = this.parser.evaluate('5');
                expect(result).toEqual(new SUGAR.expressions.ConstantExpression(5));
            });

            describe('variables', function() {
                it('should throw an error if no context given', function() {
                    let msg = 'Syntax Error: variable $myvariable without context';
                    expect(() => this.parser.evaluate('$myvariable')).toThrow(msg);
                });

                it('should return the value of the variable in the given context', function() {
                    let context = {
                        getValue: (variable) => ({'myvariable': 'my value'}[variable]),
                    };
                    expect(this.parser.evaluate('$myvariable', context)).toBe('my value');
                });
            });

            it('should throw an error if the expression does not match the expected format', function() {
                let msg = 'Syntax Error (Expression Format Incorrect \'()brackets-before-name\' )';
                expect(() => this.parser.evaluate('()brackets-before-name')).toThrow(msg);
            });
        });

        describe('getFieldsFromExpression', function() {
            it('should return fields marked with $ from expression', function() {
                let getFieldsFromExpression = this.parser.getFieldsFromExpression;
                let fields = ['field1', 'field2', 'field3'];
                let $fields = _.map(fields, function(field) {
                    return '$' + field;
                });
                let expressionWOFields = 'This formula does not contains any field';
                let expressionWithFields = 'This formula contains ' + $fields.join(', ');

                expect(getFieldsFromExpression(expressionWOFields)).toEqual([]);
                expect(getFieldsFromExpression(expressionWithFields)).toEqual(fields);
            });
        });

        describe('getType', function() {
            it('should return the type of a variable', function() {
                expect(this.parser.getType(new SUGAR.expressions.StringExpression({}))).toEqual('string');
            });

            it('should return false if given a non-expression type', function() {
                expect(this.parser.getType(5)).toEqual(false);
            });
        });

        describe('toConstant', function() {
            it('should convert numbers to ConstantExpressions', function() {
                let result = this.parser.toConstant(5);
                expect(result).toEqual(new SUGAR.expressions.ConstantExpression(5));
            });

            it('should convert pi and e to their decimal approximations', function() {
                let result = this.parser.toConstant('pi');
                expect(result).toEqual(new SUGAR.expressions.ConstantExpression(3.14159265));
                result = this.parser.toConstant('e');
                expect(result).toEqual(new SUGAR.expressions.ConstantExpression(2.718281828459045));
            });

            it('should convert string literals to string literal expressions and strip quotation marks', function() {
                let result = this.parser.toConstant('"I am a string"');
                expect(result).toEqual(new SUGAR.expressions.StringLiteralExpression('I am a string'));
                result = this.parser.toConstant('');
                expect(result).toEqual(new SUGAR.expressions.StringLiteralExpression(''));
            });

            it('should convert true and false to TrueExpressions and FalseExpressions', function() {
                let result = this.parser.toConstant('true');
                expect(result).toEqual(new SUGAR.expressions.TrueExpression());
                result = this.parser.toConstant('false');
                expect(result).toEqual(new SUGAR.expressions.FalseExpression());
            });

            it('should convert dates to DateExpressions', function() {
                // only accepts US-style month/day/year
                let result = this.parser.toConstant('05/25/2005');
                expect(result).toEqual(new SUGAR.expressions.DateExpression([5, 25, 2005]));
            });

            it('should convert times to TimeExpressions', function() {
                // only accepts 24-hour times
                let result = this.parser.toConstant('05:00:00');
                expect(result).toEqual(new SUGAR.expressions.TimeExpression([5, 0, 0]));
            });
        });

        describe('validate', function() {
            it('should throw an error if not given a string', function() {
                expect(() => this.parser.validate(5)).toThrow('ExpressionParser requires a string expression.');
            });

            it('should accept constants', function() {
                let valid = this.parser.validate('5');
                expect(valid).toBe(true);
            });

            it('should throw an error if the expression does not match the expected format', function() {
                let msg = 'Syntax Error (Expression Format Incorrect \'()brackets-before-name\' )';
                expect(() => this.parser.validate('()brackets-before-name')).toThrow(msg);
            });

            it('should accept valid expressions', function() {
                let valid = this.parser.validate('my-expression-function(my-argument)');
                expect(valid).toBe(true);
            });
        });

        describe('tokenize', function() {
            it('should return a constant token if given a constant expression', function() {
                let result = this.parser.tokenize('5');
                expect(result).toEqual({
                    type: 'constant',
                    returnType: 'number',
                    value: 5,
                });
            });

            it('should return a variable token if given a variable expression', function() {
                let result = this.parser.tokenize('$myvariable');
                expect(result).toEqual({
                    type: 'variable',
                    name: 'myvariable',
                });
            });

            it('should return a function token if given a function expression', function() {
                let result = this.parser.tokenize('myfunction($myvariable)');
                expect(result).toEqual({
                    type: 'function',
                    name: 'myfunction',
                    args: [{
                        type : 'variable',
                        name : 'myvariable',
                    }],
                });
            });

            it('should split function arguments', function() {
                let result = this.parser.tokenize('myfunction($myvariable1, $myvariable2)');
                expect(result).toEqual({
                    type: 'function',
                    name: 'myfunction',
                    args: [
                        {
                            type: 'variable',
                            name: 'myvariable1',
                        },
                        {
                            type: 'variable',
                            name: 'myvariable2',
                        },
                    ]
                });
            });
        });
    });

    describe('DateUtils', function() {
        describe('parse', function() {
            it('should return the date if given a date', function() {
                let date = new Date(Date.UTC(2017, 0, 1));
                expect(SUGAR.util.DateUtils.parse(date)).toBe(date);
            });

            it('should parse date strings', function() {
                expect(SUGAR.util.DateUtils.parse('01/01/2015', 'm/d/Y')).toEqual(new Date(2015, 0, 1));
            });
        });

        describe('guessFormat', function() {
            it('should return false if not passed a string', function() {
                expect(SUGAR.util.DateUtils.guessFormat(5)).toEqual(false);
            });

            it('should recognize USA-style month/day/year', function() {
                expect(SUGAR.util.DateUtils.guessFormat('01/25/2015')).toEqual('m/d/Y');
            });

            it('should recognize European-style year/month/day', function() {
                expect(SUGAR.util.DateUtils.guessFormat('2015/01/25')).toEqual('Y/m/d');
            });

            it('should accept periods and dashes as date separators', function() {
                expect(SUGAR.util.DateUtils.guessFormat('01-25-2015')).toEqual('m-d-Y');
                expect(SUGAR.util.DateUtils.guessFormat('01.25.2015')).toEqual('m.d.Y');
                // and nothing else
                expect(SUGAR.util.DateUtils.guessFormat('01*25*2015')).toEqual(false);
            });

            describe('times', function() {
                it('should recognize times', function () {
                    expect(SUGAR.util.DateUtils.guessFormat('01/25/2015 2:30')).toEqual('m/d/Y H:i');
                });

                it('should recognize AM/PM', function() {
                    expect(SUGAR.util.DateUtils.guessFormat('01/25/2015 2:30 am')).toEqual('m/d/Y h:i a');
                    expect(SUGAR.util.DateUtils.guessFormat('01/25/2015 2:30 AM')).toEqual('m/d/Y h:i A');
                    expect(SUGAR.util.DateUtils.guessFormat('01/25/2015 2:30AM')).toEqual('m/d/Y h:iA');
                });

                it('should accept a T as a time separator', function() {
                    expect(SUGAR.util.DateUtils.guessFormat('01/25/2015T2:30')).toEqual('m/d/YTH:i');
                });
            });
        });
    });
});
