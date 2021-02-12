<?php

namespace Formax\Formularios\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Form extends AbstractHelper
{
    protected $_logger;
    protected $_helper;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Formax\CoreApiCoopeuch\Helper\Data $helper
    )
    {
        $this->_logger = $logger;
        $this->_helper = $helper;
    }

    public function validar($data)
    {

        try {

            // Patrón para usar en expresiones regulares (admite letras acentuadas y espacios):
            $patron_texto = "/^[a-zA-ZáéíóúÁÉÍÓÚäëïöüÄËÏÖÜàèìòùÀÈÌÒÙñÑ\s]+$/";

            // Patrón para usar en expresiones regulares (formato correo electronico):
            $patron_email = "/^[_.0-9a-zA-Z-]+@([0-9a-zA-Z][0-9a-zA-Z-]+.)+[a-zA-Z]{2,6}$/";

            // Patrón para usar en expresiones regulares (formato número teléfono):
            $patron_phone = "/^[0-9]{7,8}$/";

            // Patrón para usar en expresiones regulares (formato código teléfono):
            $patron_code = "/^[0-9]{1,2}$/";

            // Patrón para usar en expresiones regulares (formato RUT):
            $patron_rut = "/^[0-9Kk\.\-]+$/";

            // Patrón para usar en expresiones regulares (admite letras minúsculas, números y guión bajo):
            $patron_formato = "/^[a-z0-9\_]+$/";

            // Patrón para usar en expresiones regulares (admite números, punto y signo $):
            $patron_currency = "/^[0-9\$\.]{1,12}$/";

            // Patrón para usar en expresiones regulares (admite letras acentuadas y espacios):
            $patron_comment = "/^[0-9a-zA-ZáéíóúÁÉÍÓÚäëïöüÄËÏÖÜñÑ\$\,\.\s\:\%\?\¿\!\¡\_\-]+$/";

            // Patrón para usar en expresiones regulares (admite números):
            $patron_number = "/[^0-9]/";

            // Patrón para usar en expresiones regulares (admite números):
            $patron_id = "/^[0-9]+$/";

            // Comprobar si llegaron los campos requeridos:
            if( isset($data['formato-id']) && 
                isset($data['rut']) && 
                isset($data['name']) && 
                isset($data['email']) && 
                isset($data['code']) && 
                isset($data['phone'])
                )
            {
                // Formato ID:
                if( empty($data['formato-id']) )
                    return false;
                else {
                    // Comprobar mediante una expresión regular, que sólo contiene letras minúsculas, números y guión bajo:
                    if( preg_match($patron_formato, $data['formato-id']) )
                        $formatoId = $data['formato-id'];
                    else
                        return false;
                }

                // Rut:
                if( empty($data['rut']) )
                    return false;
                else {
                    if( preg_match($patron_rut, $data['rut']) ) {
                        // Comprobar formato de rut:
                        if( $this->_helper->validadorRut($data['rut']) )
                            $rut = $data['rut'];
                        else
                            return false;
                    } else {
                        return false;
                    }
                }

                // Nombre:
                if( empty($data['name']) )
                    return false;
                else {
                    // Comprobar mediante una expresión regular, que sólo contiene letras y espacios:
                    if( preg_match($patron_texto, $data['name']) )
                        $name = $data['name'];
                    else
                        return false;
                }

                // Email:
                if( empty($data['email']) )
                    return false;
                else {
                    // Comprobar mediante una expresión regular, que cumple con formato de email:
                    if( preg_match($patron_email, $data['email']) )
                        $email = $data['email'];
                    else
                        return false;
                }

                // Código teléfono:
                if( empty($data['code']) )
                    return false;
                else {
                    // Comprobar mediante una expresión regular, que sólo contiene número:
                    if( preg_match($patron_code, $data['code']) )
                        $code = $data['code'];
                    else
                        return false;
                }

                // Teléfono:
                if( empty($data['phone']) )
                    return false;
                else {
                    // Comprobar mediante una expresión regular, que sólo contiene número:
                    if( preg_match($patron_phone, $data['phone']) )
                        $phone = $data['phone'];
                    else
                        return false;
                }
            }
            else
            {
                return false;
            }

            // Comprobar si viene campo apellido
            if ( isset($data['lastname']) )
            {
                if( empty($data['lastname']) )
                    return false;
                else {
                    // Comprobar mediante una expresión regular, que sólo contiene letras y espacios:
                    if( preg_match($patron_texto, $data['lastname']) )
                        $lastname = $data['lastname'];
                    else
                        return false;
                }
            }

            // Comprobar si viene campo monto
            if ( isset($data['amount']) )
            {
                if( empty($data['amount']) )
                    return false;
                else {
                    // Comprobar mediante una expresión regular, que sólo contiene número, puntos y sigo $:
                    if( preg_match($patron_currency, $data['amount']) )
                        $amount = preg_replace($patron_number, '', $data['amount']);
                    else
                        return false;
                }
            }

            // Comprobar si viene campo ingreso líquido mensual
            if ( isset($data['monthly-income']) )
            {
                if( empty($data['monthly-income']) )
                    return false;
                else {
                    // Comprobar mediante una expresión regular, que sólo contiene número, puntos y sigo $:
                    if( preg_match($patron_currency, $data['monthly-income']) )
                        $monthlyIncome = preg_replace($patron_number, '', $data['monthly-income']);
                    else
                        return false;
                }
            }

            // Comprobar si viene campo comentario
            if ( isset($data['comment']) )
            {
                if( empty($data['comment']) )
                    return false;
                else {
                    // Comprobar mediante una expresión regular, que sólo contiene número, puntos y sigo $:
                    if( preg_match($patron_comment, $data['comment']) )
                        $comment = $data['comment'];
                    else
                        return false;
                }
            }

            /**
             * Comprobar si viene campo region
             */
            if ( isset($data['region']) )
            {
                if( empty($data['region']) )
                    return false;
                else {
                    // Comprobar mediante una expresión regular, que sólo contiene letras y espacios:
                    if( preg_match($patron_texto, $data['region']) )
                        $region = $data['region'];
                    else
                        return false;
                }
            }

            /**
             * Comprobar si viene campo comuna
             */
            if ( isset($data['comuna']) )
            {
                if( empty($data['comuna']) )
                    return false;
                else {
                    // Comprobar mediante una expresión regular, que sólo contiene letras y espacios:
                    if( preg_match($patron_texto, $data['comuna']) )
                        $comuna = $data['comuna'];
                    else
                        return false;
                }
            }

            // Comprobar si viene campo id
            if ( isset($data['id']) )
            {
                // Comprobar mediante una expresión regular, que sólo contiene números:
                if( preg_match($patron_id, $data['id']) )
                    $idSolicitud = $data['id'];
                else
                    return false;
            }

            $this->_logger->info(__CLASS__. "_" .__FUNCTION__." : RUT REQUEST ".$rut);
                
            $response = $this->_helper->sendForm(
                $rut,
                $id = $idSolicitud ?? 1,
                $name,
                $lastname = $lastname ?? '',
                $email,
                $cellphone = $cellphone ?? '',
                $phone,
                $region = $region ?? '',
                $comuna = $comuna ?? '',
                $amount = $amount ?? '',
                $monthlyIncome = $monthlyIncome ?? '',
                $formatoId,
                $comment = $comment ?? '',
                $code
            );
            
            if (isset($response['solicitudesProductosResult']))
            {
                return true;
            }
            else {
                return false;
            }

        } catch (\Exception $ex) {
            $this->_logger->error("Error in REST API Return : " . $ex->getMessage());
        }

        return false;
    }
}