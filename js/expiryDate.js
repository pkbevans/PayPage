const expMonth = document.getElementById('card_expirationMonth');
const expYear = document.getElementById('card_expirationYear');

const focusSibling = function (target, direction, callback) {
    const nextTarget = target[direction];
    nextTarget && nextTarget.focus();
    // if callback is supplied we return the sibling target which has focus
    callback && callback(nextTarget);
}

expYear.addEventListener('change', (event) => {
    fieldsValid(true);
});
expMonth.addEventListener('change', (event) => {
    fieldsValid(true);
});
function expiryDateValid() {
    d = new Date();
    todayYear = d.getFullYear();
    todayMonth = d.getMonth();
    xMonth = parseInt(expMonth.value);
    xYear = 2000 + parseInt(expYear.value);
    if (xYear < todayYear || (xYear === todayYear && xMonth < todayMonth) || xMonth > 12 || xMonth < 1) {
        return false;
    }
    return true;
}

// input event only fires if there is space in the input for entry.
// If an input of x length has x characters, keyboard press will not fire this input event.
expMonth.addEventListener('input', (event) => {

    const value = event.target.value.toString();
    // adds 0 to month user input like 9 -> 09
    if (value.length === 1 && value > 1) {
        event.target.value = "0" + value;
    }
    // bounds
    if (value === "00") {
        event.target.value = "01";
    } else if (value > 12) {
        event.target.value = "12";
    }
    // if we have a filled input we jump to the year input
    2 <= event.target.value.length && focusSibling(event.target, "nextElementSibling");
    event.stopImmediatePropagation();
});

expYear.addEventListener('keydown', (event) => {
    // if the year is empty jump to the month input
    if (event.key === "Backspace" && event.target.selectionStart === 0) {
        focusSibling(event.target, "previousElementSibling");
        event.stopImmediatePropagation();
    }
});

const inputMatchesPattern = function (e) {
    const {
        value,
        selectionStart,
        selectionEnd,
        pattern
    } = e.target;

    const character = String.fromCharCode(e.which);
    const proposedEntry = value.slice(0, selectionStart) + character + value.slice(selectionEnd);
    const match = proposedEntry.match(pattern);

    return e.metaKey || // cmd/ctrl
            e.which <= 0 || // arrow keys
            e.which == 8 || // delete key
            match && match["0"] === match.input; // pattern regex isMatch - workaround for passing [0-9]* into RegExp
};

document.querySelectorAll('input[data-pattern-validate]').forEach(el => el.addEventListener('keypress', e => {
        if (!inputMatchesPattern(e)) {
            return e.preventDefault();
        }
    }));
