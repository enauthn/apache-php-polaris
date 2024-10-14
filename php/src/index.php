<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        /* Basic styling */
        .button {
            padding: 10px 20px;
            margin: 10px;
            border: none;
            background-color: #007bff;
            color: white;
            cursor: pointer;
            border-radius: 4px;
        }

        .button:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <?php echo "Hello World!" ?>

    <!-- File upload form -->
    <form id="uploadForm" enctype="multipart/form-data">
        <label for="fileInput">Upload a file:</label>
        <input type="file" id="fileInput" name="fileInput" accept="*/*">
        <br>
        <!-- Connect button -->
        <button type="button" id="connectButton" class="button">Connect</button>
        <!-- Attest button, initially disabled -->
        <button type="button" id="attestButton" class="button" disabled>Attest</button>
        <button type="button" id="downloadButton" class="button" disabled>Download Attestation</button>
    </form>

    <!-- Load the client.js file as a module -->
    <script type="module">
        import { createClient } from './polaris-web/client.js';

        // Initialize client
        const client = createClient({ targetOrigin: '*' });

        // Reference to the file input, connect button, attest button, and download button
        const fileInput = document.getElementById('fileInput');
        const connectButton = document.getElementById('connectButton');
        const attestButton = document.getElementById('attestButton');
        const downloadButton = document.getElementById('downloadButton');
        let attestCredResult = null;
        let isAuthorized = false;

        // Connect button click handler
        connectButton.addEventListener('click', async function() {
            try {
                const isInstalled = await client.isExtensionInstalled();
                console.log("Is the extension installed? ", isInstalled);

                if (isInstalled) {
                    const authResult = await client.authorize({ message: "Please authorize the app" });
                    console.log("Authorization Result: ", authResult);
                    isAuthorized = true;
                    alert('Connected successfully!');
                    attestButton.disabled = fileInput.files.length === 0; // Enable attestation if a file is selected
                } else {
                    console.log("Extension not installed.");
                    alert('Extension not installed. Please install the required extension.');
                }
            } catch (error) {
                console.error("Error during authorization: ", error);
                alert("Authorization failed. Please try again.");
            }
        });

        // Enable the attest button when a file is uploaded, only if connected
        fileInput.addEventListener('change', function() {
            attestButton.disabled = !isAuthorized || fileInput.files.length === 0;
        });

        // Attest button click handler
        attestButton.addEventListener('click', async function() {
            if (fileInput.files.length > 0) {
                const file = fileInput.files[0];

                try {
                    // Proceed with attesting the file after authorization
                    attestCredResult = await attestFile(file);
                    if (attestCredResult) {
                        downloadButton.disabled = false;
                    }
                } catch (error) {
                    console.error("Error during attestation: ", error);
                    alert("Attestation failed. Please try again.");
                }
            }
        });

        // Attest the file function
        async function attestFile(file) {
            console.log('Attesting file:', file.name);
            let schemaSaid = 'EB4AsU1rKGOAf7m4MS324XhanXq8G01sR_bUdUV2TULm';
            let credData = { digest: await digestFile(file) };

            try {
                const result = await client.createDataAttestationCredential({
                    credData: credData,
                    schemaSaid: schemaSaid
                });
                console.log('Attestation Result: ', result);
                return result;
            } catch (error) {
                console.error('Error attesting file:', error);
                return null;
            }
        }

        // Helper function to compute the file digest (SHA-256)
        async function digestFile(file) {
            const arrayBuffer = await file.arrayBuffer();
            const hashBuffer = await crypto.subtle.digest('SHA-256', arrayBuffer);
            const hashArray = Array.from(new Uint8Array(hashBuffer));
            return hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
        }

        // Download attested credential
        downloadButton.addEventListener('click', async function() {
            if (attestCredResult) {
                try {
                    let credSAID = attestCredResult.acdc?._ked?.d;
                    const credential = await client.getCredential(credSAID, true);
                    if (!credential?.credential) {
                        console.error("Unable to get credential");
                        return;
                    }
                    const blob = new Blob([credential.credential], { type: 'text/plain' });
                    const url = URL.createObjectURL(blob);
                    const link = document.createElement('a');
                    link.href = url;
                    link.download = 'attestation-credential.cesr';
                    link.click();
                    URL.revokeObjectURL(url);
                } catch (error) {
                    console.error("Error downloading credential: ", error);
                }
            }
        });
    </script>
    
</body>
</html>
