</div><!-- /container -->

<footer style="
    background: #1a3d2b;
    color: rgba(255,255,255,.65);
    padding: 28px 0;
    margin-top: 60px;
    font-family: 'DM Sans', sans-serif;
    font-size: .85rem;
">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">

            <!-- Logo + copyright -->
            <div class="d-flex align-items-center gap-2">
                <span style="font-size:1.2rem;">🐾</span>
                <span style="color:#fff;font-weight:600;font-size:.95rem;">VétoCare</span>
                <span style="color:rgba(255,255,255,.3);">·</span>
                <span>&copy; <?= date('Y') ?> Tous droits réservés</span>
            </div>

            <!-- Links -->
            <div class="d-flex gap-4">
                <a href="#" style="color:rgba(255,255,255,.55);text-decoration:none;transition:color .2s;"
                   onmouseover="this.style.color='#74c69d'" onmouseout="this.style.color='rgba(255,255,255,.55)'">
                    Mentions légales
                </a>
                <a href="#" style="color:rgba(255,255,255,.55);text-decoration:none;transition:color .2s;"
                   onmouseover="this.style.color='#74c69d'" onmouseout="this.style.color='rgba(255,255,255,.55)'">
                    Contact
                </a>
                <a href="#" style="color:rgba(255,255,255,.55);text-decoration:none;transition:color .2s;"
                   onmouseover="this.style.color='#74c69d'" onmouseout="this.style.color='rgba(255,255,255,.55)'">
                    À propos
                </a>
            </div>

        </div>
    </div>
</footer>

<script>
function validerMotDePasse() {
    const mdp = document.getElementById('mot_de_passe');
    const confirm = document.getElementById('confirm_mdp');
    if (mdp && confirm) {
        mdp.value !== confirm.value
            ? confirm.setCustomValidity('Les mots de passe ne correspondent pas !')
            : confirm.setCustomValidity('');
    }
}

function aperçuPhoto(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
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
    document.querySelectorAll('.alert').forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 3000);
    });
});

function confirmerSuppression(message) {
    return confirm(message || 'Voulez-vous vraiment supprimer cet élément ?');
}
</script>

</body>
</html>