<style>
    * {
        margin: 0;
        padding: 0;
    }

    @keyframes pulse {
        0% {
            transform: scale(0.95);
            opacity: 0.7;
        }

        50% {
            transform: scale(1);
            opacity: 1;
        }

        100% {
            transform: scale(0.95);
            opacity: 0.7;
        }
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    @keyframes fadeOut {
        from {
            opacity: 1;
        }

        to {
            opacity: 0;
        }
    }

    .fadeOut {
        animation: fadeOut 0.3s ease forwards;
    }

    .pulsePreloaderAnimation {
        animation: pulse 1s infinite;
    }

    #spinner {
        width: 100px;
        height: 100px;
        border: 5px solid rgba(0, 0, 0, 0.2);
        border-top: 5px solid #e3b432;
        border-radius: 50%;
        animation: spin 0.75s linear infinite;
    }
</style>
<?php
    $theme="#fff";
    if(isset($_COOKIE['theme'])) $theme = $_COOKIE['theme']==="light"?"#fff":"#212529"; 
?>
<div id="preloaderGlobalOnPage"
    style="width:100vw;height:100vh;background-color: <?php echo $theme;?>;position:fixed;z-index:9999;display:flex;align-items:center;justify-content:center;">
    <div id="spinner"></div>
</div>
<script>
    window.addEventListener("load", () => {
        setTimeout(() => document.getElementById("preloaderGlobalOnPage").style.display="none",750);
    });
</script>