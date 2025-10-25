<!-- Modal HTML -->
<div id="logoutModal" style="
    display: none; 
    position: fixed; 
    top: 0; left: 0; 
    width: 100%; height: 100%; 
    background: rgba(0,0,0,0.5); 
    justify-content: center; 
    align-items: center;
    z-index: 1000;
">
    <div style="
        background-color: #fff; 
        padding: 20px; 
        border-radius: 8px; 
        max-width: 400px; 
        width: 90%; 
        text-align: center;
        box-shadow: 0 0 10px rgba(0,0,0,0.3);
    ">
        <p style="font-weight: bold; font-size: 16px; color: #FF3F1A;">
            Did you save all the changes you made?
        </p>
        <button onclick="nextConfirm()" style="margin: 10px; padding: 5px 15px;">Yes</button>
        <button onclick="closeModal()" style="margin: 10px; padding: 5px 15px;">No</button>
    </div>
</div>

<script>
    const modal = document.getElementById('logoutModal');

    function showModal() {
        modal.style.display = 'flex';
    }

    function closeModal() {
        modal.style.display = 'none';
    }

    function nextConfirm() {
        if(confirm('Are you sure you want to log out?')) {
            window.location.href='sorcomweb_login.php'; // PHP logout page
        } else {
            closeModal();
        }
    }
</script>

<!-- Logout Button -->
<button class="sidebar-button" 
        style="margin-top: 90px; color: black; background-color: #FF3F1A;" 
        onclick="showModal()">
    Logout
</button>