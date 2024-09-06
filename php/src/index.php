<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <?php echo "Hello World!" ?>

    <!-- Add type="module" here -->
    <!-- <script type="module" src="/polaris-web/client.js"></script> -->
    <!-- Load the client.js file as a module -->
    <script type="module">
        import { createClient } from '../polaris-web/client.js';

        // Use the createClient method
        const client = createClient({ targetOrigin: '*' });

        // usage of the client
        async function initClient() {
            try {
                const isInstalled = await client.isExtensionInstalled();
                console.log("Is the extension installed? ", isInstalled);

                if (isInstalled) {
                    const authResult = await client.authorize({ message: "Please authorize the app" });
                    console.log("Authorization Result: ", authResult);
                } else {
                    console.log("Extension not installed");
                }
            } catch (error) {
                console.error("Error in client initialization: ", error);
            }
        }

        // Call the function to initialize the client
        initClient();
    </script>

</body>
</html>
