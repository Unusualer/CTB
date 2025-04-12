// Cookie helper functions (same as in login.php)
function setCookie(name, value, days) {
    let expires = "";
    if (days) {
        const date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        expires = "; expires=" + date.toUTCString();
    }
    document.cookie = name + "=" + (value || "") + expires + "; path=/; SameSite=Strict";
    console.log(`Cookie set: ${name}=${value}, expires in ${days} days`);
}

function getCookie(name) {
    const nameEQ = name + "=";
    const ca = document.cookie.split(';');
    for (let i = 0; i < ca.length; i++) {
        let c = ca[i];
        while (c.charAt(0) === ' ') c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
    }
    return null;
}

function eraseCookie(name) {
    document.cookie = name + '=; Max-Age=-99999999; path=/';
    console.log(`Cookie erased: ${name}`);
}

// Test functions
function testRememberMeFunctionality() {
    console.log("Testing Remember Me Functionality");

    // Current cookies
    console.log("Current cookies:", document.cookie);

    // Test cookie setting
    setCookie("ctb_email", "test@example.com", 30);

    // Verify cookie was set
    const savedEmail = getCookie("ctb_email");
    console.log("Retrieved email cookie:", savedEmail);

    // Test cookie erasure
    eraseCookie("ctb_email");

    // Verify cookie was erased
    const afterErase = getCookie("ctb_email");
    console.log("After erasure, email cookie:", afterErase);
}

// Run test
testRememberMeFunctionality();
