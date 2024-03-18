import fs from "fs";
import laravel from "laravel-vite-plugin";
import { defineConfig } from "vite";
import { homedir } from "os";
import { resolve } from "path";

let host = "unmeb.test";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                "resources/js/multi-select.js",
                // "resources/js/sweetalert.all.js",
            ],
            refresh: true,
        }),
    ],
});

// function detectServerConfig(host) {
//     let keyPath = resolve(homedir(), `.config/valet/Certificates/${host}.key`);
//     let certificatePath = resolve(
//         homedir(),
//         `.config/valet/Certificates/${host}.crt`
//     );

//     if (!fs.existsSync(keyPath)) {
//         return {};
//     }

//     if (!fs.existsSync(certificatePath)) {
//         return {};
//     }

//     return {
//         hmr: { host },
//         host,
//         https: {
//             key: fs.readFileSync(keyPath),
//             cert: fs.readFileSync(certificatePath),
//         },
//     };
// }
