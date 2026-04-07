function toggleYearSelect() {
        const yearType = document.getElementById('year_type').value;
        const civilYearSelect = document.getElementById('civil_year');
        const schoolYearSelect = document.getElementById('school_year');

        if (yearType === 'civil') {
            schoolYearSelect.value = '';
            schoolYearSelect.classList.add('hidden');
            schoolYearSelect.required = false;
            civilYearSelect.classList.remove('hidden');
            civilYearSelect.required = true;
        } else if (yearType === 'school') {
            civilYearSelect.value = '';
            civilYearSelect.classList.add('hidden');
            civilYearSelect.required = false;
            schoolYearSelect.classList.remove('hidden');
            schoolYearSelect.required = true;
        } else {
            civilYearSelect.value = '';
            schoolYearSelect.value = '';
            civilYearSelect.classList.add('hidden');
            schoolYearSelect.classList.add('hidden');
            civilYearSelect.required = false;
            schoolYearSelect.required = false;
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const yearType = document.getElementById('year_type').value;
        if (yearType === 'civil' && document.getElementById('civil_year').value) {
            toggleYearSelect();
        } else if (yearType === 'school' && document.getElementById('school_year').value) {
            toggleYearSelect();
        } else {
            toggleYearSelect();
        }

        document.getElementById('civil_year').addEventListener('change', function() {
            if (this.value) {
                document.getElementById('school_year').value = '';
            }
        });

        document.getElementById('school_year').addEventListener('change', function() {
            if (this.value) {
                document.getElementById('civil_year').value = '';
            }
        });
    });