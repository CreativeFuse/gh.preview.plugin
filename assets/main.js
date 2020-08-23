
function ghentSetup() {
    window.$Ghent = {};
    window.$Ghent.addToCartBtn = "button.single_add_to_cart_button.button.alt.jupiterx-icon-shopping-cart-6";
}

// add elements to view and approve the preview if it was created
function ghentCreatePreview( target, data ) {
    ghentDisableAddToCart();

    var previewUrl = ghentGetPreviewUrl( data[0].url );

    if ( ghentPreviewExists( previewUrl ) === false ) {
        ghentEnableAddToCart();
        return;
    }

    window.$Ghent.verificationCheckbox = ghentCreateCheckbox( 'ghent-cfi-verify-preview', 'Preview Approved', ['cb-ghent-approve'] );
    window.$Ghent.verificationCheckbox.addEventListener( 'click', ghentPreviewApprovalCheck );
    target.element.insertAdjacentElement( 'afterend', window.$Ghent.verificationCheckbox );

    window.$Ghent.previewButton = ghentCreateButton( 'ghent-cfi-view-preview', 'View Preview', ['btn-link'] );
    window.$Ghent.previewButton.dataset.link = previewUrl;
    window.$Ghent.previewButton.addEventListener( 'click', ghentOpenPreviewLink );
    target.element.insertAdjacentElement( 'afterend', window.$Ghent.previewButton );
}

// test if the preview pdf exists
function ghentPreviewExists( url ) {
    var preview = new XMLHttpRequest();
    preview.open( 'HEAD', url, false );
    preview.send();
    return preview.status != 404;
}

// handle the approval checkbox clicks
function ghentPreviewApprovalCheck() {
    if ( document.getElementById('ghent-preview-approved').checked ) {
        ghentEnableAddToCart();
    } else {
        ghentDisableAddToCart();
    }

}

// disable add-to-cart button
function ghentDisableAddToCart(){
    document.querySelector( window.$Ghent.addToCartBtn ).disabled = true;
}

// enable add-to-cart button
function ghentEnableAddToCart(){
    document.querySelector( window.$Ghent.addToCartBtn ).disabled = false;
}

// open the preview in a new tab/window
function ghentOpenPreviewLink( e ) {
    e.preventDefault();
    window.open( this.dataset.link, '_blank' );
}

// create a button to display the preview
function ghentCreateButton( id, btnText, classList ) {
    const button = document.createElement('button');
    button.id = id;
    button.type = 'button';
    classList.forEach( (btnClass) => {
        button.classList.add(btnClass);
    });
    button.innerText = btnText;
    return button;
}

// create a checkbox for preview approval
function ghentCreateCheckbox( id, labelText, classList ) {
    const label = document.createElement( 'label' );
    label.id = id;
    label.htmlFor = 'ghent-preview-approved';
    label.appendChild( document.createTextNode( labelText ) );
    classList.forEach( (lblClass) => {
        label.classList.add(lblClass);
    });

    const checkbox = document.createElement( 'input' );
    checkbox.type = 'checkbox';
    checkbox.id = 'ghent-preview-approved';
    checkbox.name = 'ghent-preview-approved';
    checkbox.value = 'approved';

    label.insertAdjacentElement( 'afterbegin', checkbox );

    return label;
}

// parse the logo url to get the preview url
function ghentGetPreviewUrl( logoUrl ) {
    var fileName = logoUrl.split('/').pop();
    var logoPlacement = document.getElementById( 'logo-placement' ).value;
    if ( logoPlacement.length <= 0 ) {
        return false;
    }
    logoPlacement = logoPlacement.replace( ' ', '-' ).toLowerCase();

    var logoBaseName = fileName.split('.')[0];
    var newFileName = 'preview-' + logoPlacement + '_' + logoBaseName + '.pdf';
    return logoUrl.replace( fileName, newFileName );
}

// clear our elements when the logo is removed
function ghentRemovePreview() {
    window.$Ghent.previewButton.remove();
    window.$Ghent.verificationCheckbox.remove();
    ghentEnableAddToCart();
}

if ( document.readyState === 'loading' ) {
    document.addEventListener('DOMContentLoaded', ghentSetup);
} else {
    ghentSetup();
}
