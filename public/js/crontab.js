
document.addEventListener('DOMContentLoaded', function() {
    const inputRange = document.getElementById('input_range');
    inputRange.addEventListener('input', function() {
        const value = this.value.trim();
        const numberPattern = /^\\d+$/;
        const rangePattern = /^\\d+\\s*-\\s*\\d+$/;

        if (numberPattern.test(value) || rangePattern.test(value) || value === '') {
            this.classList.remove('is-invalid');
            this.classList.add('is-valid');
        } else {
            this.classList.remove('is-valid');
            this.classList.add('is-invalid');
        }
    });
});
