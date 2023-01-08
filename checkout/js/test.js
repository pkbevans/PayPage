import { Selector } from 'testcafe';

fixture`Getting Started`
    .page`https://site.test/paypage/index.php`;

test('My first test', async t => {
    // Test code
    await t
        .typeText('#amount', '12.32');
        // .click('#checkoutButton');
});
