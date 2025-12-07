<style>
.pricing-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 30px;
    margin: 40px 0;
}

.pricing-card {
    background-color: #111111;
    border: 2px solid #333333;
    border-radius: 8px;
    padding: 30px;
    transition: transform 0.3s ease, border-color 0.3s ease;
}

.pricing-card:hover {
    transform: translateY(-5px);
    border-color: #FFFF00;
}

.pricing-card.featured {
    border-color: #FFFF00;
    background-color: #1a1a00;
}

.plan-name {
    font-size: 28px;
    color: #FFFF00;
    margin-bottom: 10px;
}

.plan-price {
    font-size: 42px;
    color: #FFFFFF;
    margin-bottom: 5px;
}

.plan-price span {
    font-size: 18px;
    color: #AAAAAA;
}

.plan-description {
    color: #AAAAAA;
    margin-bottom: 25px;
    min-height: 60px;
}

.plan-features {
    list-style: none;
    padding: 0;
    margin-bottom: 30px;
}

.plan-features li {
    padding: 10px 0;
    border-bottom: 1px solid #222222;
    color: #CCCCCC;
}

.plan-features li:before {
    content: "✓ ";
    color: #FFFF00;
    font-weight: bold;
    margin-right: 10px;
}

.featured-badge {
    background-color: #FFFF00;
    color: #000000;
    padding: 5px 15px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: bold;
    display: inline-block;
    margin-bottom: 15px;
}
</style>

<h1>Choose Your Security Plan</h1>
<p style="color: #AAAAAA; margin-bottom: 20px;">Select the plan that best fits your privacy and security needs.</p>

<div class="pricing-container">
    <!-- Basic Plan -->
    <div class="pricing-card">
        <h2 class="plan-name">Basic Shield</h2>
        <div class="plan-price">$5<span>/month</span></div>
        <p class="plan-description">Perfect for individuals seeking essential privacy protection.</p>
        
        <ul class="plan-features">
            <li>Single device connection</li>
            <li>Standard encryption (AES-128)</li>
            <li>5 server locations</li>
            <li>Unlimited bandwidth</li>
            <li>Email support</li>
            <li>Basic ad blocking</li>
        </ul>
        
        <form action="index.php?page=cart" method="post" style="margin-top: 15px;">
            <input type="hidden" name="plan_name" value="Basic Shield">
            <input type="hidden" name="price" value="5.00">
            <input type="hidden" name="billing_cycle" value="monthly">
            <input type="hidden" name="duration_days" value="30">
            <button type="submit" class="btn-primary" style="display:block; text-align:center; width:100%;">Add to Cart</button>
        </form>
    </div>
    
    <!-- Premium Plan -->
    <div class="pricing-card featured">
        <span class="featured-badge">MOST POPULAR</span>
        <h2 class="plan-name">Premium Guard</h2>
        <div class="plan-price">$12<span>/month</span></div>
        <p class="plan-description">Advanced protection for power users and small teams.</p>
        
        <ul class="plan-features">
            <li>5 simultaneous devices</li>
            <li>Military-grade encryption (AES-256)</li>
            <li>50+ server locations worldwide</li>
            <li>Unlimited bandwidth</li>
            <li>Priority 24/7 support</li>
            <li>Advanced ad & malware blocking</li>
            <li>Kill switch protection</li>
            <li>Split tunneling</li>
        </ul>
        
        <form action="index.php?page=cart" method="post" style="margin-top: 15px;">
            <input type="hidden" name="plan_name" value="Premium Guard">
            <input type="hidden" name="price" value="12.00">
            <input type="hidden" name="billing_cycle" value="monthly">
            <input type="hidden" name="duration_days" value="30">
            <button type="submit" class="btn-primary" style="display:block; text-align:center; width:100%;">Add to Cart</button>
        </form>
    </div>
    
    <!-- Enterprise Plan -->
    <div class="pricing-card">
        <h2 class="plan-name">Enterprise Fortress</h2>
        <div class="plan-price">$25<span>/month</span></div>
        <p class="plan-description">Ultimate security for businesses and large organizations.</p>
        
        <ul class="plan-features">
            <li>Unlimited devices</li>
            <li>Military-grade encryption (AES-256)</li>
            <li>100+ global server locations</li>
            <li>Unlimited bandwidth</li>
            <li>Dedicated account manager</li>
            <li>Advanced threat protection</li>
            <li>Custom security policies</li>
            <li>Dedicated IP addresses</li>
            <li>Team management dashboard</li>
            <li>API access</li>
        </ul>
        
        <form action="index.php?page=cart" method="post" style="margin-top: 15px;">
            <input type="hidden" name="plan_name" value="Enterprise Fortress">
            <input type="hidden" name="price" value="25.00">
            <input type="hidden" name="billing_cycle" value="monthly">
            <input type="hidden" name="duration_days" value="30">
            <button type="submit" class="btn-primary" style="display:block; text-align:center; width:100%;">Add to Cart</button>
        </form>
    </div>
</div>

<div style="background-color: #111111; padding: 20px; border-radius: 8px; margin-top: 40px;">
    <h3 style="color: #FFFF00; margin-bottom: 10px;">All Plans Include:</h3>
    <p style="color: #AAAAAA;">✓ No logs policy &nbsp;&nbsp; ✓ Money-back guarantee &nbsp;&nbsp; ✓ Secure DNS &nbsp;&nbsp; ✓ Regular security audits</p>
</div>