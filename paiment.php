<?php
session_start();
include __DIR__ . '/db.php'; // DB connection if needed

// Bank/RIP info
$bank_name = "biat";
$bank_owner = "******";
$bank_rip  = "*******";

// Amount example
$amount = 50; 
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Paiement</title>
    <link rel="stylesheet" href="/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <script src="https://js.stripe.com/v3/"></script>
    <style>
        :root {
            --primary-color: #38d39f;
            --primary-dark: #2eb389;
            --background-light: #f0faf5;
            --card-bg: #ffffff;
            --shadow-light: rgba(56, 211, 159, 0.15);
            --shadow-strong: rgba(56, 211, 159, 0.35);
        }
        body { font-family: 'Inter', sans-serif; background: var(--background-light); margin: 0; padding: 0; }
        .container { max-width: 900px; margin: 50px auto; }
        h1 { color: var(--primary-color); text-align: center; margin-bottom: 30px; }
        .card { background: var(--card-bg); border-radius: 16px; padding: 30px; box-shadow: 0 8px 28px var(--shadow-light); margin-bottom: 30px; }
        .card h3 { color: var(--primary-dark); margin-bottom: 20px; }
        .btn-primary { background: var(--primary-color); border: none; border-radius: 8px; padding: 10px 20px; font-weight: 600; transition: 0.3s; }
        .btn-primary:hover { background: var(--primary-dark); }
        .back-btn { display: block; text-align: center; margin-top: 20px; text-decoration: none; color: white; background: var(--primary-color); padding: 10px 20px; border-radius: 8px; transition: 0.3s; }
        .back-btn:hover { background: var(--primary-dark); }
        .bank-info { background: #e6f4ea; border-left: 6px solid var(--primary-color); padding: 20px; border-radius: 10px; position: relative; }
        .bank-info p { margin: 8px 0; font-weight: 600; }
        .copy-btn { position: absolute; top: 20px; right: 20px; background: var(--primary-color); color: #fff; border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer; transition: 0.3s; }
        .copy-btn:hover { background: var(--primary-dark); }
        input, textarea { width: 100%; padding: 10px; margin: 8px 0 16px; border-radius: 8px; border: 1px solid #ccc; font-size: 1rem; }
        .toggle-btn { background: var(--primary-color); color: white; border: none; padding: 10px 18px; margin-bottom: 20px; border-radius: 8px; cursor: pointer; font-weight: 600; transition: 0.3s; }
        .toggle-btn:hover { background: var(--primary-dark); }
    </style>
</head>
<body>

<div class="container">
    <h1>Paiement</h1>

    <button class="toggle-btn" id="togglePayment">Afficher le paiement en ligne</button>

    <!-- Bank Transfer / RIP info (shown first) -->
    <div class="card" id="bankPayment">
        <h3>Paiement par virement bancaire</h3>
        <div class="bank-info">
            <p><strong>Banque :</strong> <?php echo $bank_name; ?></p>
            <p><strong>Nom du propriétaire :</strong> <?php echo $bank_owner; ?></p>
            <p><strong>RIP / IBAN :</strong> <span id="rip"><?php echo $bank_rip; ?></span></p>
            <p>Vous pouvez effectuer un virement directement à ce compte. Mentionnez votre nom et la description de votre paiement.</p>
            <button class="copy-btn" onclick="copyRIP()">Copier RIP</button>
        </div>
    </div>

    <!-- Payer en ligne (hidden initially) -->
    <div class="card" id="onlinePayment" style="display:none;">
        <h3>Payer en ligne(non disponible pour le moment) </h3>
        <form id="payment-form" method="POST" action="process_payment.php">
            <label for="name">Votre Nom</label>
            <input type="text" name="name" id="name" required>

            <label for="email">Votre Email</label>
            <input type="email" name="email" id="email" required>

            <label for="amount">Montant (€)</label>
            <input type="number" name="amount" id="amount" value="<?php echo $amount; ?>" required>

            <label for="description">Description</label>
            <textarea name="description" id="description" rows="3" placeholder="Votre commande..."></textarea>

            <div class="mb-3">
                <label for="card-element">Carte bancaire</label>
                <div id="card-element" class="form-control"></div>
                <div id="card-errors" role="alert" style="color:red;margin-top:5px;"></div>
            </div>

            <button type="submit" class="btn btn-primary w-100">Payer maintenant</button>
        </form>
    </div>

    <!-- Back Button -->
    <a href="dashboard.php" class="back-btn">Retour à l'accueil</a>
</div>

<script>
const stripe = Stripe('pk_test_YOUR_PUBLISHABLE_KEY'); // Replace with your publishable key
const elements = stripe.elements();
const card = elements.create('card', { hidePostalCode: true });
card.mount('#card-element');

card.on('change', function(event) {
    const displayError = document.getElementById('card-errors');
    displayError.textContent = event.error ? event.error.message : '';
});

const form = document.getElementById('payment-form');
form.addEventListener('submit', async function(event) {
    event.preventDefault();
    const {token, error} = await stripe.createToken(card);

    if(error){
        document.getElementById('card-errors').textContent = error.message;
    } else {
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'stripeToken';
        hiddenInput.value = token.id;
        form.appendChild(hiddenInput);
        form.submit();
    }
});

// Toggle online / bank payment
document.getElementById('togglePayment').addEventListener('click', function(){
    const online = document.getElementById('onlinePayment');
    const bank = document.getElementById('bankPayment');
    if(online.style.display === 'none'){
        online.style.display = 'block';
        bank.style.display = 'none';
        this.textContent = "Afficher le paiement par virement";
    } else {
        online.style.display = 'none';
        bank.style.display = 'block';
        this.textContent = "Afficher le paiement en ligne";
    }
});

function copyRIP() {
    const ripText = document.getElementById('rip').textContent;
    navigator.clipboard.writeText(ripText).then(() => {
        alert('RIP/IBAN copié !');
    });
}
</script>

</body>
</html>
