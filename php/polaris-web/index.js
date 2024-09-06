import * as polaris_web from "./client.js";

$(document).ready(function(){

    initTokenEdgeClient();
});

async function initTokenEdgeClient(){
    let client = await new polaris_web.ExtensionClient();
    const result = client.authorizeCred();
    console.log( resault );
}