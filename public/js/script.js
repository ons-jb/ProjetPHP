function validerMotDePasse() {
    const mdp = document.getElementById('mot_de_passe');
    const confirm = document.getElementById('confirm_mdp');
    if (mdp && confirm) {
        if (mdp.value !== confirm.value) {
            confirm.setCustomValidity('Les mots de passe ne correspondent pas !');
        } else {
            confirm.setCustomValidity('');
        }
    }
}

function aperçuPhoto(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const apercu = document.getElementById('apercu_photo');
            if (apercu) {
                apercu.src = e.target.result;
                apercu.style.display = 'block';
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function (alert) {
        setTimeout(function () {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 3000);
    });
});

function confirmerSuppression(message) {
    return confirm(message || 'Voulez-vous vraiment supprimer cet élément ?');
}