if ( ! window.wasaKreditMonthlyCostWidget ) {
    var monthlyCostWidget = {
        showWasaKreditFinancingModal: function ( guid, monthlyCost ) {
            const es = document.querySelector( `.wasa-kredit-information-overlay[data-id='${ guid }']` )
            es.setAttribute( "style", "display:block;" )
            if ( window.wasaKreditAnalyticsTracker ) {
                window.wasaKreditAnalyticsTracker.trackEvent( {
                    action: "Read more",
                    category: "Monthly Cost Widget",
                    label: "Monthly Cost Widget 2.0",
                    value: monthlyCost,
                } )
                window.wasaKreditAnalyticsTracker.lastOpenModal = Math.floor( Date.now() / 1000 )
            }
        },
        closeWasaKreditFinancingModal: function ( e ) {
            if ( e.target === e.currentTarget ) {
                const es = document.getElementsByClassName( "wasa-kredit-information-overlay" )
                for ( let i = 0; i < es.length; i++ ) {
                    es[ i ].removeAttribute( "style" )
                }
                if ( window.wasaKreditAnalyticsTracker ) {
                    const overlay_open_in_seconds =
                        Math.floor( Date.now() / 1000 ) - window.wasaKreditAnalyticsTracker.lastOpenModal
                    window.wasaKreditAnalyticsTracker.trackEvent( {
                        action: "Closed",
                        category: "Monthly Cost Widget",
                        label: "Monthly Cost Widget 2.0",
                        value: overlay_open_in_seconds,
                    } )
                }
            }
        },
    }
    window.wasaKreditMonthlyCostWidget = monthlyCostWidget

    if ( wasaKreditParams !== undefined ) {
        jQuery( function ( $ ) {
            const widget = {
                updating: false,
                init: function () {
                    $( document ).on( "found_variation", widget.onVariationChange )
                    $( "form.cart" ).on( "change", "input.qty", widget.onQuantityChange )
                },

                update_monthly_widget: function ( price ) {
                    const url = new URL( wasaKreditParams.wasa_kredit_update_monthly_widget_url, window.location )
                    $.ajax( {
                        url: url.href,
                        type: "POST",
                        data: {
                            price: price.toFixed( 2 ),
                            nonce: wasaKreditParams.wasa_kredit_update_monthly_widget_nonce,
                        },
                        dataType: "json",
                        crossDomain: false,
                        success: function ( res ) {
                            const container = $( ".wasa-kredit-product-widget-container" )
                            container.replaceWith( $.parseHTML( res.data ) )
                            widget.updating = false
                        },
                        complete: function ( res ) {
                            console.log( res )
                        },
                    } )
                },

                onQuantityChange: function () {
                    if ( widget.updating ) {
                        return
                    }
                    widget.updating = true
                    const { thousand_separator, decimal_separator } = wasaKreditParams
                    const quantity = parseInt( $( this ).val() )

                    let price = $( "form.cart .woocommerce-variation-price .amount" )
                    if ( 0 === price.length ) {
                        price = $( ".summary .price .amount" )
                    }
                    const unit_price = parseFloat(
                        price.text().replace( thousand_separator, "" ).replace( decimal_separator, "." ),
                    )
                    if ( quantity > 0 ) {
                        const total_price = quantity * unit_price
                        if ( ! isNaN( total_price ) ) {
                            widget.update_monthly_widget( total_price )
                        }
                    }
                },

                onVariationChange: function ( e, variation ) {
                    if ( widget.updating ) {
                        return
                    }
                    widget.updating = true

                    let price = Math.round( variation.display_price )
                    const quantity = parseInt( $( "form.cart input[name=quantity]" ).val() )
                    if ( ! isNaN( quantity ) ) {
                        price *= quantity
                    }
                    widget.update_monthly_widget( price )
                },
            }
            widget.init()
        } )
    }
}
