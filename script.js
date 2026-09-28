// Le menu reste visible si JavaScript est désactivé.
document.documentElement.classList.add('js');
const menuButton = document.querySelector('.menu-toggle');
const navigation = document.getElementById('main-nav');
menuButton.hidden = false;
menuButton.addEventListener('click', () => {
  // toggle ajoute ou retire la classe ; sa valeur de retour indique si le menu est ouvert.
  const isOpen = navigation.classList.toggle('is-open');
  // Les lecteurs d'écran ont aussi besoin de connaître l'état du menu.
  menuButton.setAttribute('aria-expanded', String(isOpen));
});
// Sur mobile, refermer le menu après avoir choisi une section.
navigation.querySelectorAll('a').forEach((link) => {
  link.addEventListener('click', () => {
    navigation.classList.remove('is-open');
    menuButton.setAttribute('aria-expanded', 'false');
  });
});
document.addEventListener('keydown', (event) => {
  // Échap ferme le menu et replace le focus sur son bouton pour la navigation au clavier.
  if (event.key === 'Escape' && navigation.classList.contains('is-open')) {
    navigation.classList.remove('is-open');
    menuButton.setAttribute('aria-expanded', 'false');
    menuButton.focus();
  }
});
document.getElementById('year').textContent = new Date().getFullYear();

const contactForm = document.getElementById('contactForm');
const formMessage = document.getElementById('formMessage');
contactForm.addEventListener('submit', async (event) => {
  // Empêcher le rechargement habituel : fetch enverra le formulaire en arrière-plan.
  event.preventDefault();
  const submitButton = contactForm.querySelector('button[type="submit"]');
  const formData = new FormData(contactForm);
  // FormData récupère les champs par leur attribut name.
  // trim enlève les espaces aux extrémités ; some vérifie si au moins un champ est vide.
  if (['name', 'email', 'message'].some((field) => !formData.get(field).trim())) {
    formMessage.textContent = 'Please fill in all fields.';
    formMessage.dataset.state = 'error';
    return;
  }
  // Éviter plusieurs clics pendant que la requête est en cours.
  submitButton.disabled = true;
  formMessage.textContent = 'Sending your message…';
  formMessage.dataset.state = '';
  try {
    // POST transmet les champs au PHP. Accept demande une réponse au format JSON.
    // await attend la réponse sans bloquer les autres interactions sur la page.
    const response = await fetch(contactForm.action, {
      method: 'POST', body: formData, headers: { Accept: 'application/json' }
    });
    const result = await response.json();
    // Vérifier à la fois le statut HTTP et le résultat annoncé par le serveur.
    if (!response.ok || !result.success) throw new Error(result.message || 'Your message could not be sent. Please try again.');
    formMessage.textContent = result.message;
    formMessage.dataset.state = 'success';
    // Vider les champs seulement après confirmation du serveur.
    contactForm.reset();
  } catch (error) {
    // En cas de problème réseau ou de réponse illisible, conserver le texte saisi.
    formMessage.textContent = error instanceof SyntaxError || error instanceof TypeError
      ? 'The contact service is unavailable. Please try again later.' : error.message;
    formMessage.dataset.state = 'error';
  } finally {
    // Ce bloc s'exécute aussi en cas d'erreur : le bouton doit redevenir utilisable.
    submitButton.disabled = false;
  }
});
