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
const {Agent, Fixtures} = require('@sugarcrm/thorn');
const chakram = require('chakram');
const expect = chakram.expect;

describe('Filters.Float', function() {
    before(function*() {
        let records = {attributes: {user_name: 'John', status: 'Active'}};

        records = yield Fixtures.create(records, {module: 'Users'});

        this.johnId = records.Users[0].id;

        records = [
            {attributes: {cost_price: '0.100000', assigned_user_id: this.johnId}},
            {attributes: {cost_price: '123.450000', assigned_user_id: this.johnId}},
            {attributes: {cost_price: '537.000000', assigned_user_id: this.johnId}},
        ];

        this.records = yield Fixtures.create(records, {module: 'Products'});

        [this.product1, this.product2, this.product3] = this.records.Products;
    });

    after(function*() {
        yield Fixtures.cleanup();
    });

    it('should filter records whose field value exactly matches given value', function*() {
        let response = yield Agent.as('John').get('Products', {
            max_num: 2,
            qs: {
                filter: [{
                    assigned_user_id: this.johnId,
                    cost_price: this.product2.cost_price,
                }]
            },
        });

        expect(response).to.have.json('records', (records) => {
            expect(records).to.have.length(1);
            expect(records[0].cost_price).to.be.equal(this.product2.cost_price);
        });
    });

    it('should filter records whose field value does not match given value', function*() {
        let response = yield Agent.as('John').get('Products', {
            max_num: 3,
            qs: {
                filter: [{
                    assigned_user_id: this.johnId,
                    cost_price: {'$not_equals': this.product2.cost_price},
                }]
            },
        });

        expect(response).to.have.json('records', (records) => {
            let product1 = records.find((r) => r.id === this.product1.id);
            let product3 = records.find((r) => r.id === this.product3.id);

            expect(records).to.have.length(2);
            expect(product1).to.exist;
            expect(product3).to.exist;
        });
    });

    it('should filter records whose field value is greater than given value', function*() {
        let response = yield Agent.as('John').get('Products', {
            max_num: 2,
            qs: {
                filter: [{
                    assigned_user_id: this.johnId,
                    cost_price: {'$gt': this.product2.cost_price},
                }]
            },
        });

        expect(response).to.have.json('records', (records) => {
            expect(records).to.have.length(1);
            expect(records[0].cost_price).to.be.equal(this.product3.cost_price);
        });
    });

    it('should filter records whose field value is less than given value', function*() {
        let response = yield Agent.as('John').get('Products', {
            max_num: 2,
            qs: {
                filter: [{
                    assigned_user_id: this.johnId,
                    cost_price: {'$lt': this.product2.cost_price},
                }]
            },
        });

        expect(response).to.have.json('records', (records) => {
            expect(records).to.have.length(1);
            expect(records[0].cost_price).to.be.equal(this.product1.cost_price);
        });
    });

    it('should filter records whose field value is greater than or equal to', function*() {
        let response = yield Agent.as('John').get('Products', {
            max_num: 3,
            qs: {
                filter: [{
                    assigned_user_id: this.johnId,
                    cost_price: {'$gte': this.product2.cost_price},
                }]
            },
        });

        expect(response).to.have.json('records', (records) => {
            let product2 = records.find((r) => r.id === this.product2.id);
            let product3 = records.find((r) => r.id === this.product3.id);

            expect(records).to.have.length(2);
            expect(product2).to.exist;
            expect(product3).to.exist;
        });
    });

    it('should filter records whose field value is less than or equal to', function*() {
        let response = yield Agent.as('John').get('Products', {
            max_num: 3,
            qs: {
                filter: [{
                    assigned_user_id: this.johnId,
                    cost_price: {'$lte': this.product2.cost_price},
                }]
            },
        });

        expect(response).to.have.json('records', (records) => {
            let product1 = records.find((r) => r.id === this.product1.id);
            let product2 = records.find((r) => r.id === this.product2.id);

            expect(records).to.have.length(2);
            expect(product1).to.exist;
            expect(product2).to.exist;
        });
    });

    it('should filter records whose field value is between given values', function*() {
        let response = yield Agent.as('John').get('Products', {
            max_num: 2,
            qs: {
                filter: [{
                    assigned_user_id: this.johnId,
                    cost_price: {'$between': ['0.500000', '536.000000']},
                }]
            },
        });

        expect(response).to.have.json('records', (records) => {
            expect(records).to.have.length(1);
            expect(records[0].cost_price).to.be.equal(this.product2.cost_price);
        });
    });
});
