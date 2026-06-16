/* =============================================
   LANDING PAGE — logique principale
   ============================================= */

// --- Effet machine à écrire ---
const pressEnter = document.querySelector('.press_enter');
if (pressEnter) {
    const fullText = pressEnter.textContent;
    pressEnter.textContent = '';
    let i = 0;
    const type = () => {
        if (i < fullText.length) {
            pressEnter.textContent += fullText[i++];
            setTimeout(type, 65);
        }
    };
    setTimeout(type, 600);
}

// --- Bouton Start (swap image) ---
const btnStart = document.getElementById('btnStart');
if (btnStart) {
    btnStart.addEventListener('mouseenter', () => { btnStart.src = 'src/assets/button_on.png'; });
    btnStart.addEventListener('mouseleave', () => { btnStart.src = 'src/assets/button_off.png'; });
}

// --- Modales ---
document.getElementById('btnAbout').addEventListener('click', () => {
    document.getElementById('modalAbout').classList.add('open');
});
document.getElementById('btnContact').addEventListener('click', () => {
    document.getElementById('modalContact').classList.add('open');
});

document.querySelectorAll('.landing-modal-close').forEach(btn => {
    btn.addEventListener('click', () => {
        document.getElementById(btn.dataset.close).classList.remove('open');
    });
});
document.querySelectorAll('.landing-modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', e => {
        if (e.target === overlay) overlay.classList.remove('open');
    });
});
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        document.querySelectorAll('.landing-modal-overlay.open')
            .forEach(m => m.classList.remove('open'));
    }
});

// --- Formulaire contact ---
document.getElementById('contactForm').addEventListener('submit', async e => {
    e.preventDefault();
    const form     = e.target;
    const feedback = document.getElementById('contactFeedback');
    const btn      = form.querySelector('.contact-submit');

    const name    = form.name.value.trim();
    const email   = form.email.value.trim();
    const message = form.message.value.trim();

    if (!name || !email || !message) {
        feedback.textContent = '⚠ Tous les champs sont obligatoires.';
        feedback.className   = 'contact-feedback err';
        return;
    }
    if (!/^[\p{L}\p{M}\s'\-\.]{1,80}$/u.test(name)) {
        feedback.textContent = '⚠ Nom invalide (lettres, espaces, tirets uniquement).';
        feedback.className   = 'contact-feedback err';
        return;
    }
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        feedback.textContent = '⚠ Adresse email invalide.';
        feedback.className   = 'contact-feedback err';
        return;
    }
    if (message.length > 2000) {
        feedback.textContent = '⚠ Message trop long (2000 caractères max).';
        feedback.className   = 'contact-feedback err';
        return;
    }

    btn.disabled    = true;
    btn.textContent = '...';

    try {
        const res  = await fetch('api/contact.php', { method: 'POST', body: new URLSearchParams(new FormData(form)) });
        const data = await res.json();
        if (data.success) {
            feedback.textContent = '✓ Message envoyé !';
            feedback.className   = 'contact-feedback ok';
            form.reset();
        } else {
            feedback.textContent = '⚠ ' + (data.error ?? 'Erreur inconnue');
            feedback.className   = 'contact-feedback err';
        }
    } catch {
        feedback.textContent = '⚠ Connexion impossible';
        feedback.className   = 'contact-feedback err';
    }

    btn.disabled    = false;
    btn.textContent = 'Envoyer';
});
