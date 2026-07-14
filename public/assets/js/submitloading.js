function attachFormSubmitLoader(formId, buttonId) {
    const form = document.getElementById(formId);
    const button = document.getElementById(buttonId);

    if (!form || !button) {
        console.warn('Form or button not found for:', formId, buttonId);
        return;
    }

    form.addEventListener('submit', function (e) {
        if (!form.checkValidity()) {
            e.preventDefault();
            return false;
        }

        button.disabled = true;
        button.textContent = 'Submitting...';
    });
}
