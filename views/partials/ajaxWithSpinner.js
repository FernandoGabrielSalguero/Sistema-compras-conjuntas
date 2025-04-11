async function fetchConSpinner(url, options = {}) {
    try {
        showSpinner();
        const response = await fetch(url, options);
        return response;
    } finally {
        hideSpinner();
    }
}