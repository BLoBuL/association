import { chromium } from 'file:///C:/Users/Jul/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/.pnpm/playwright@1.60.0/node_modules/playwright/index.mjs';

const baseUrl = 'https://dev.blobul.com/spip.php?page=evenement&id_evenement=';
const loginUrl = 'https://dev.blobul.com/spip.php?page=login';
const unique = Date.now();

const anonymousScenarios = [
  {
    id: 183,
    name: 'public_gratuit_sans_validation',
    expectedTitleText: 'Inscription ouverte pour ce rendez-vous',
    expectedFormAction: 'inscription_evenement_multi_public',
    steps: [
      {
        fields: {
          prenom_inscrit_1: 'Test',
          nom_inscrit_1: 'Gratuit',
          email_inscrit_1: `test-gratuit-${unique}@example.test`,
          telephone_inscrit_1: '0600000001',
        },
        submitLabel: 'Suivant',
        expectText: 'Récapitulatif',
      },
    ],
  },
  {
    id: 228,
    name: 'public_gratuit_file_attente_active',
    expectedTitleText: 'Inscription ouverte pour ce rendez-vous',
    expectedFormAction: 'inscription_evenement_multi_public',
    expectedBodyTexts: ['Atelier parents-enfants'],
  },
  {
    id: 184,
    name: 'public_payant_validation_sur_paiement',
    expectedTitleText: 'Inscription ouverte pour ce rendez-vous',
    expectedFormAction: 'inscription_evenement_multi_public',
    expectedBodyTexts: ['Participation demandée pour ce rendez-vous'],
    steps: [
      {
        fields: {
          prenom_inscrit_1: 'Test',
          nom_inscrit_1: 'Validation',
          email_inscrit_1: `test-validation-${unique}@example.test`,
          telephone_inscrit_1: '0600000003',
        },
        submitLabel: 'Suivant',
        expectText: 'Modalités',
      },
    ],
  },
  {
    id: 187,
    name: 'strict_gratuit_anonyme_bloque',
    expectedTitleText: "L'inscription en ligne à ce rendez-vous est réservée aux membres de l'association authentifiés",
    expectedNoFormAction: 'inscription_evenement_multi_public',
  },
  {
    id: 188,
    name: 'strict_payant_anonyme_bloque',
    expectedTitleText: "L'inscription en ligne à ce rendez-vous est réservée aux membres de l'association authentifiés",
    expectedNoFormAction: 'inscription_evenement_multi_public',
  },
  {
    id: 202,
    name: 'strict_gratuit_accompagnants_anonyme_bloque',
    expectedTitleText: "L'inscription en ligne à ce rendez-vous est réservée aux membres de l'association authentifiés",
    expectedNoFormAction: 'inscription_evenement_multi_public',
  },
  {
    id: 224,
    name: 'strict_payant_accompagnants_anonyme_bloque',
    expectedTitleText: "L'inscription en ligne à ce rendez-vous est réservée aux membres de l'association authentifiés",
    expectedNoFormAction: 'inscription_evenement_multi_public',
  },
];

const authenticatedScenarios = [
  {
    id: 187,
    name: 'strict_gratuit_connecte_sans_accompagnants',
    expectedTitleText: 'Inscription ouverte pour ce rendez-vous',
    expectedFormAction: 'inscription_evenement_multi_public',
    expectedSelectors: ['input[name="famille"][value="adherent"]'],
    forbiddenSelectors: ['input[name="famille[]"][value="enfant_1"]'],
  },
  {
    id: 188,
    name: 'strict_payant_connecte_sans_accompagnants',
    expectedTitleText: 'Inscription ouverte pour ce rendez-vous',
    expectedFormAction: 'inscription_evenement_multi_public',
    expectedBodyTexts: ['Participation demandée pour ce rendez-vous'],
    expectedSelectors: ['input[name="famille"][value="adherent"]'],
    forbiddenSelectors: ['input[name="famille[]"][value="enfant_1"]'],
  },
  {
    id: 202,
    name: 'strict_gratuit_connecte_famille',
    expectedTitleText: 'Inscription ouverte pour ce rendez-vous',
    expectedFormAction: 'inscription_evenement_multi_public',
    expectedSelectors: [
      'input[name="famille[]"][value="adherent"]',
      'input[name="famille[]"][value="conjoint"]',
      'input[name="famille[]"][value="enfant_1"]',
      'input[name="famille[]"][value="enfant_2"]',
    ],
  },
  {
    id: 224,
    name: 'strict_payant_connecte_famille',
    expectedTitleText: 'Inscription ouverte pour ce rendez-vous',
    expectedFormAction: 'inscription_evenement_multi_public',
    expectedBodyTexts: ['Participation demandée pour ce rendez-vous'],
    expectedSelectors: [
      'input[name="famille[]"][value="adherent"]',
      'input[name="famille[]"][value="conjoint"]',
      'input[name="famille[]"][value="enfant_1"]',
      'input[name="famille[]"][value="enfant_2"]',
    ],
  },
];

function log(result) {
  process.stdout.write(`${result}\n`);
}

function hasVisiblePhpError(text) {
  return text.includes('Deprecated:') || text.includes('Fatal error') || text.includes('Warning:');
}

async function fillVisibleFields(page, fields) {
  for (const [name, value] of Object.entries(fields)) {
    const locator = page.locator(`[name="${name}"]`).first();
    if (await locator.count()) {
      await locator.fill(value);
    }
  }
}

async function clickButtonByText(page, label) {
  const button = page.getByRole('button', { name: label }).first();
  await button.click();
}

async function assertBaseState(page, scenario, errors) {
  const bodyText = await page.locator('body').innerText();

  if (hasVisiblePhpError(bodyText)) {
    errors.push('La page affiche une erreur PHP visible');
  }

  if (scenario.expectedTitleText && !bodyText.includes(scenario.expectedTitleText)) {
    errors.push(`Texte attendu manquant: ${scenario.expectedTitleText}`);
  }

  for (const text of scenario.expectedBodyTexts || []) {
    if (!bodyText.includes(text)) {
      errors.push(`Texte attendu manquant: ${text}`);
    }
  }

  const hasExpectedFormAction = await page
    .locator(`input[name="formulaire_action"][value="${scenario.expectedFormAction || 'inscription_evenement_multi_public'}"]`)
    .count();

  if (scenario.expectedFormAction && !hasExpectedFormAction) {
    errors.push(`Formulaire attendu absent: ${scenario.expectedFormAction}`);
  }

  if (scenario.expectedNoFormAction && hasExpectedFormAction) {
    errors.push(`Formulaire non attendu present: ${scenario.expectedNoFormAction}`);
  }

  for (const selector of scenario.expectedSelectors || []) {
    if (!(await page.locator(selector).count())) {
      errors.push(`Selecteur attendu absent: ${selector}`);
    }
  }

  for (const selector of scenario.forbiddenSelectors || []) {
    if (await page.locator(selector).count()) {
      errors.push(`Selecteur non attendu present: ${selector}`);
    }
  }
}

async function runScenario(browser, scenario, context = {}) {
  const page = context.page || (await browser.newPage());
  const createdPage = !context.page;
  const errors = [];
  const consoleErrors = [];

  page.on('console', (msg) => {
    if (msg.type() === 'error') {
      consoleErrors.push(msg.text());
    }
  });
  page.on('pageerror', (err) => {
    consoleErrors.push(String(err));
  });

  try {
    await page.goto(`${baseUrl}${scenario.id}`, { waitUntil: 'networkidle' });
    await assertBaseState(page, scenario, errors);

    for (const step of scenario.steps || []) {
      await fillVisibleFields(page, step.fields);
      await clickButtonByText(page, step.submitLabel);
      await page.waitForLoadState('networkidle');

      const stepText = await page.locator('body').innerText();
      if (step.expectText && !stepText.includes(step.expectText)) {
        errors.push(`Apres "${step.submitLabel}", texte attendu manquant: ${step.expectText}`);
      }
      if (hasVisiblePhpError(stepText)) {
        errors.push(`Erreur PHP visible apres "${step.submitLabel}"`);
      }
    }

    if (consoleErrors.length) {
      errors.push(`Erreurs console: ${consoleErrors.join(' | ')}`);
    }

    const status = errors.length ? 'KO' : 'OK';
    log(`${status}|${scenario.id}|${scenario.name}|${errors.join(' ; ')}`);
  } catch (error) {
    log(`KO|${scenario.id}|${scenario.name}|Exception: ${String(error)}`);
  } finally {
    if (createdPage) {
      await page.close();
    }
  }
}

async function loginWithMember(browser) {
  const login = process.env.BLOBUL_TEST_LOGIN;
  const password = process.env.BLOBUL_TEST_PASSWORD;

  if (!login || !password) {
    log('SKIP|auth|login_manquant|Variables BLOBUL_TEST_LOGIN/BLOBUL_TEST_PASSWORD absentes');
    return null;
  }

  const page = await browser.newPage();
  await page.goto(loginUrl, { waitUntil: 'networkidle' });
  await page.fill('#var_login', login);
  await page.fill('#password', password);
  await page.click('input.submit[value="Valider"]');
  await page.waitForLoadState('networkidle');

  const bodyText = await page.locator('body').innerText();
  const errors = [];

  if (!page.url().includes('page=profil')) {
    errors.push(`Redirection de connexion inattendue: ${page.url()}`);
  }
  if (!bodyText.includes('BONJOUR')) {
    errors.push('Connexion non confirmee par le profil');
  }
  if (hasVisiblePhpError(bodyText)) {
    errors.push('Erreur PHP visible apres connexion');
  }

  if (errors.length) {
    log(`KO|auth|login|${errors.join(' ; ')}`);
    await page.close();
    return null;
  }

  log(`OK|auth|login|${login}`);
  return page;
}

const browser = await chromium.launch({ headless: true });
try {
  for (const scenario of anonymousScenarios) {
    await runScenario(browser, scenario);
  }

  const authPage = await loginWithMember(browser);
  if (authPage) {
    try {
      for (const scenario of authenticatedScenarios) {
        await runScenario(browser, scenario, { page: authPage });
      }
    } finally {
      await authPage.close();
    }
  }
} finally {
  await browser.close();
}
