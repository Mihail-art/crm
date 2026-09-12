import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';

document.addEventListener('DOMContentLoaded', () => {
    const status = document.body.dataset.status;
    const redirectTo = document.body.dataset.statusRedirect;

    if (!status) {
        return;
    }

    Swal.fire({
        icon: 'success',
        title: 'Успіх!',
        text: status,
        confirmButtonText: 'Гаразд',
        timer: 3000,
        timerProgressBar: true,
    }).then(() => {
        if (redirectTo) {
            window.location.href = redirectTo;
        }
    });
});
