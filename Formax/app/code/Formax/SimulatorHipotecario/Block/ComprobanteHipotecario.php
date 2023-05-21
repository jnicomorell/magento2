<?php
namespace Formax\SimulatorHipotecario\Block;
use Formax\SimulatorHipotecario\Helper\Data;

class ComprobanteHipotecario extends \Magento\Framework\View\Element\Template
{
	protected $_plazo;
	protected $_amount;
	protected $_storeManager;
	protected $_helper;
	protected $_name;
	protected $_lname;
	protected $_rut;
	protected $_email;
	protected $date;
	protected $_monthlyincome;
	protected $_tasasFactory;
	protected $crosseling;

	public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
		\Formax\Crypto\Helper\Data $cryptohelper,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Framework\Stdlib\DateTime\TimezoneInterface $date,
		Data $helperData,
		\Formax\MortgageSimulator\Model\RateFactory $rateFactory,
		\Formax\CrosselingCampaigns\Helper\Data $crosseling
	)
	{
		$this->_helper = $helperData;
		$this->_cryptohelper = $cryptohelper;
		$this->_storeManager = $storeManager;
		$this->date = $date;
		$this->_tasasFactory = $rateFactory;
		$this->crosseling = $crosseling;
		parent::__construct($context);
		$this->init();
	}

	public function init($plazo=null)
	{
		$post = $this->getRequest()->getPostValue();
		//print_r($post);
		//exit;
		if($post){
			$this->_prefix=$post['code'];
			$this->_phone=$post['phone'];
			$this->_amount=$post['amount'];
			$this->_amount=$this->getAmount();
			$this->_monthlyincome=$post['monthly-income'];
			$this->_plazo=$post['plazo'];
			if(isset($post['pie'])){
				$this->_pievalue=filter_var($post['pie'], FILTER_SANITIZE_NUMBER_INT);
			}
			$this->_morepie='si';
			if(isset($post['more_pie'])){
				$this->_morepie=$post['more_pie'];
			}
			$this->_rut=$post['rut'];
			$this->_email=$post['email'];
			$this->_name=$post['name'];
			if(isset($post['lastname'])){
				$this->_lname=$post['lastname'];
			}
			if($plazo){
				$this->_plazo=$plazo;
			}
			$this->rateblock= $this->getLayout()->createBlock('Formax\MortgageSimulator\Block\Rates');
			$this->termblock= $this->getLayout()->createBlock('Formax\MortgageSimulator\Block\Terms');
			$rates = $this->rateblock->getRateCollection();
			$ufData=$this->getUfData();
			$this->_precioventauf=round($this->_amount/$ufData);
			if($this->_morepie=='si'){
				$this->_pie=$this->_amount*0.2;
			}else{
				$this->_pie=$this->_amount*(($this->_pievalue*100)/$this->_amount)/100;
			}
			$this->_pieuf=round($this->_pie/$ufData);
			$this->_montoinicialuf=round($this->_precioventauf-$this->_pieuf);
			$this->_saldoinsoluto=$this->_montoinicialuf*$ufData;
			$this->tasacion="2.50";
			$this->estudio="3.0";
			$this->escritura="1.0";
			$this->notaria="3.0";
			$this->maxcbruf=128000000/$ufData;
			//=IF($this->_montoinicialuf>$this->maxcbruf,$this->maxcbruf*(0.8/100),$this->_montoinicialuf*(0.95/100))
			$this->cbr=$this->_montoinicialuf>$this->maxcbruf?($this->maxcbruf*(0.8/100)):($this->_montoinicialuf*(0.95/100));
			//$this->cbr=($this->_saldoinsoluto>128000000?(128000000*1/100):($this->_saldoinsoluto*1/100))/$ufData;
			$this->seguroincsismo=($this->_precioventauf*75/100)*(0.0082/100);
			$tarifas[5]=array();
			$tarifas[8]=array();
			$tarifas[10]=array();
			$tarifas[12]=array();
			$tarifas[15]=array();
			$tarifas[18]=array();
			$tarifas[20]=array();
			$tarifas[25]=array();
			$tarifas[30]=array();
			foreach($rates as $rate){
				if($rate->getFiveYears()){
					array_push($tarifas[5], $rate->getFiveYears());
				}
				if($rate->getEightYears()){
					array_push($tarifas[8], $rate->getEightYears());
				}
				if($rate->getTenYears()){
					array_push($tarifas[10], $rate->getTenYears());
				}
				if($rate->getTwelveYears()){
					array_push($tarifas[12], $rate->getTwelveYears());
				}
				if($rate->getFifteenYears()){
					array_push($tarifas[15], $rate->getFifteenYears());
				}
				if($rate->getEighteenYears()){
					array_push($tarifas[18], $rate->getEighteenYears());
				}
				if($rate->getTwentyYears()){
					array_push($tarifas[20], $rate->getTwentyYears());
				}
				if($rate->getTwentyFiveYears()){
					array_push($tarifas[25], $rate->getTwentyFiveYears());
				}
				if($rate->getThirtyYears()){
					array_push($tarifas[30], $rate->getThirtyYears());
				}

			}
			$plazotarifado=array("5"=>"5", "6"=>"8", "7"=>"8", "8"=>"8", "9"=>"10", "10"=>"10", "11"=>"12", "12"=>"12", "13"=>"15", "14"=>"15", "15"=>"15", "16"=>"18", "17"=>"18", "18"=>"18", "19"=>"20", "20"=>"20", "21"=>"25", "22"=>"25", "23"=>"25", "24"=>"25", "25"=>"25", "26"=>"30", "27"=>"30", "28"=>"30", "29"=>"30", "30"=>"30");
			/*if($this->_plazo >= 25){
				$plazotarifado=$plazotarifado["25"];
			}
			if($this->_plazo >= 30){
				$plazotarifado=$plazotarifado["30"];
			}*/
			//if($this->_plazo < 25){
				$plazotarifado=$plazotarifado[$this->_plazo];
			//}
			$tasafija=$tarifas;
			$tasafijaOP=0;
			if($this->_montoinicialuf>=4000 && $this->_montoinicialuf<=99999){
				$tasafijaOP=6;
			}
			if($this->_montoinicialuf>=3000 && $this->_montoinicialuf<=4999){
				$tasafijaOP=6;
			}
			if($this->_montoinicialuf>=2000 && $this->_montoinicialuf<=2999){
				$tasafijaOP=5;
			}
			if($this->_montoinicialuf>=1500 && $this->_montoinicialuf<=1999){
				$tasafijaOP=4;
			}
			if($this->_montoinicialuf>=1000 && $this->_montoinicialuf<=1499){
				$tasafijaOP=3;
			}
			if($this->_montoinicialuf>=750 && $this->_montoinicialuf<=999){
				$tasafijaOP=2;
			}
			if($this->_montoinicialuf>=500 && $this->_montoinicialuf<=749){
				$tasafijaOP=1;
			}
			if($this->_montoinicialuf>=0 && $this->_montoinicialuf<=499){
				$tasafijaOP=0;
			}

			$this->_tasaanual=$tasafija[$plazotarifado][$tasafijaOP];
			//$this->_tasamensual=pow(($this->_tasaanual), (1/12)-1);
			$this->_tasamensual=(pow(($this->_tasaanual*0.01)+1,1/12)-1);

			//$this->_tasamensual=0.29516;
			$this->_PMT=$this->pmt($this->_tasamensual,$this->_plazo*12,($this->_montoinicialuf));
			//$this->_cuotasinseguro=$this->floorDec($this->_PMT,3,'.')-0.0001;
			$this->_cuotasinseguro=$this->_PMT;
			$this->_primaseguro=($this->_precioventauf*75/100)*(0.0082/100);
			$this->_primasegurodgv=($this->_montoinicialuf*(0.0141/100));
			$this->_dividendomensual=$this->_cuotasinseguro+$this->_primaseguro+$this->_primasegurodgv;
			$this->cae=$this->getCae();

		}
	}

	/**
	 * Esta funcion retorna los datos de una simulacion completa
	 * Debe recibir valor monto, el tipo del monto (peso o uf),
	 * el valor del pie, el tipo del pie (peso o uf) y el plazo en años
	 */
	public function simular($monto, $tipoMonto, $pie, $tipoPie, $plazo)
	{
		$ufValue = $this->getUfData();

		if($tipoMonto == 'peso') {
			$montoPeso = $monto;
			$montoUf   = round($monto / $ufValue);
		} else {
			$montoUf = $monto;
		}

		if($tipoPie == 'peso') {
			$piePeso = $pie;
			$pieUf   = round($this->sanitizeNumber($pie) / $this->sanitizeNumber($ufValue));
		} else {
			$pieUf = $pie;
		}

		$plazos = $this->getTermOption($plazo);
		array_unshift($plazos, $plazo);
		$simulacion=array();
		foreach($plazos as $key => $plazoItem){
			$simulacion[$key]=(object)[];
			switch ($plazoItem) {
				case 5:
					$col = "five_years";
					break;
				case 8:
					$col = "eight_years";
					break;
				case 10:
					$col = "ten_years";
					break;
				case 12:
					$col = "twelve_years";
					break;
				case 15:
					$col = "fifteen_years";
					break;
				case 18:
					$col = "eighteen_years";
					break;
				case 20:
					$col = "twenty_years";
					break;
				case 25:
					$col = "twenty_five_years";
					break;
				case 30:
					$col = "thirty_years";
					break;			
				default:
					$col = "five_years";
			}
			$montoCredito = $montoUf - $pieUf;
			$montoCredito = round($montoCredito);
			$tasas = $this->_tasasFactory->create();
			$collection = $tasas->getCollection($col);
			$collection->addFieldToSelect($col);
			$collection->addFieldToFilter('amount_since', array('lteq' => $montoCredito));
			$collection->addFieldToFilter('amount_to', array('gteq' => $montoCredito));
			$collection->setPageSize(1);
			$financiacion = 100-$this->getPercentPie();
			$simulacion[$key]->valoruf=$this->getValorActualUf();
			$simulacion[$key]->dividendo=number_format($this->getDividendo($plazoItem), 2,',','.');
			$simulacion[$key]->dividendoPeso=number_format($this->getDividendo($plazoItem)*$this->getValorActualUf(), 0,',','.');
			$simulacion[$key]->plazo=$plazoItem;
			foreach ($collection as $item)
			{
				$simulacion[$key]->tasa=number_format($item->getData($col),2,',','.');
			}
			$simulacion[$key]->cae=$this->getCaeTir($plazoItem);
			$simulacion[$key]->ctc=number_format($this->getCtc($plazoItem),'2',',','.');
			$simulacion[$key]->ctcPeso=number_format($this->getCtc($plazoItem)*$this->getValorActualUf(), 0,',','.');
			$simulacion[$key]->financiaporcentaje=number_format(100-$this->getPercentPie(), 0, ',', '.')."%";
			$simulacion[$key]->financiamonto=($this->getValorPropiedad()*$financiacion/100);
			$simulacion[$key]->financiamontoPeso=number_format(($this->getValorPropiedad()*$financiacion/100)*$this->getValorActualUf(), 0,',','.');
			$simulacion[$key]->pieporcentaje=$this->getPercentPie();
			$simulacion[$key]->piemonto=($this->getValorPropiedad()*(100-$financiacion)/100);
			$simulacion[$key]->piemontoPeso=number_format(($this->getValorPropiedad()*(100-$financiacion)/100)*$this->getValorActualUf(),0,',','.');
			$simulacion[$key]->gastos=$this->getGastosOperacionales();
			$simulacion[$key]->tasacion=2.5;
			$simulacion[$key]->estudio=3;
			$simulacion[$key]->borrador=1;
			$simulacion[$key]->notaria=3;
			$simulacion[$key]->impuestomutuo=6.24;
			$simulacion[$key]->inscripcionconservador=10.26;
			$simulacion[$key]->segurodesgravamen=$this->getSeguroDesgravamen();
			$simulacion[$key]->seguroincendio=$this->getSeguroIncendio();

		}
		return $simulacion;

	}

	public function sendMessage()
	{
		try {
			parent::send($this->_message);
		} catch (\Exception $e) {
			throw new \Magento\Framework\Exception\MailException(new \Magento\Framework\Phrase($e->getMessage()), $e);
		}
	}
	public function getValorPropiedad($plazo=null){
		$this->init($plazo);
		return $this->_precioventauf;
	}
	public function getValorActualUf($plazo=null){
		$this->init($plazo);
		return $this->getUfData();
	}
	public function getPie($plazo=null){
		$this->init($plazo);
		return $this->_pie;
	}
	public function getMorePie($plazo=null){
		return $this->_morepie;
	}
	public function getPercentPie(){
		$percent = $this->_pie*100/$this->_amount;
		$percent = $percent;
		return $percent;
	}
	public function getPieUf($plazo=null){
		$this->init($plazo);
		return $this->_pieuf;
	}
	public function getTasaAnual($plazo=null){
		$this->init($plazo);
		return $this->_tasaanual;
	}
	public function getSeguroDesgravamen($plazo=null){
		$this->init($plazo);
		return $this->_primasegurodgv;
	}
	public function getSeguroIncendio($plazo=null){
		$this->init($plazo);
		return $this->_primaseguro;
	}
	public function getDividendo($plazo=null){
		$this->init($plazo);
		return $this->_dividendomensual;
	}	
	public function getPrefix(){
		return $this->_prefix;
	}
	public function getPhone(){
		return $this->_phone;
	}
	public function getMonthlyIncome(){
		return $this->_monthlyincome;
	}
	public function getGastosOperacionales($plazo=null){
		$this->init($plazo);
		return $this->tasacion+$this->estudio+$this->escritura+$this->notaria+$this->cbr;
	}
	public function getPlazo(){
        return filter_var($this->_plazo, FILTER_SANITIZE_NUMBER_INT);
	}
    public function getAmount(){
		return filter_var($this->_amount, FILTER_SANITIZE_NUMBER_INT);
	}		
	public function getRut(){
        return filter_var($this->_rut, FILTER_SANITIZE_STRING);
	}
	public function getName(){
        return filter_var($this->_name, FILTER_SANITIZE_STRING);
	}
	public function getLastName(){
        return filter_var($this->_lname, FILTER_SANITIZE_STRING);
	}
    public function getEmail(){
        return filter_var($this->_email, FILTER_SANITIZE_STRING);
	}
	
	public function getUfData(){
		$uf = $this->rateblock->getUfCollection();
		$ufData="";
		foreach($uf as $ufvalue){
			$ufData=$ufvalue->getUfValue();
		}
		if(empty($ufData)){
			$ufData=28700;
		}
		return $ufData;
	}

	public function ufFormat($ufdata){
		return number_format($ufdata,2,',','.')." UF";
	}

	/*public function pmt($apr, $loanlength, $loanamount){
		$apr = $apr/100;
		$loanlength = 360;
		$loanamount = 1394;
		return ($apr * -$loanamount * pow((1 + $apr), 12) / (1 - pow((1 + $apr), 12)));
	}*/
	public function pmt($interest,$num_of_payments,$PV,$FV = 0.00, $Type = 0){

		//((((3,60×0,01)+1)^(1÷12))−1)×100
		//$interest=(pow(($this->_tasaanual*0.01)+1,1/12)-1)*100;
		$interest=(pow(($this->_tasaanual*0.01)+1,1/12)-1);
		$xp=pow((1+$interest),$num_of_payments);
		return
			($PV* $interest*$xp/($xp-1)+$interest/($xp-1)*$FV)*
			($Type==0 ? 1 : 1/($interest+1));
	}
	public function getCae($plazo=0) {
		$tasamensual=$this->_tasamensual;
		$saldo[]=-$this->_montoinicialuf;
		$cuotasinseguro=$this->_cuotasinseguro;
		$iteresmensual[]=0;
		$amortizacion[]=0;
		$cuotaconseguros[]=0;
		$primaseguro=$this->_primaseguro;
		$ctc[]=0;
		$cae[]=0;
		$caesum[]=0;
		$plazomeses = $plazo?$plazo:$this->_plazo;
		for ($i = 1; $i <= $plazomeses; $i++) {
			$iteresmensual[$i]=$saldo[$i-1]*($tasamensual*0.01);
			$amortizacion[$i]=$cuotasinseguro+$iteresmensual[$i];
			$saldo[$i]=$saldo[$i-1]+$amortizacion[$i];
			if($i==1){
				$primadesgravamen[$i]=$saldo[$i-1]*(0.0141/100)*2;
			}else{
				$primadesgravamen[$i]=$saldo[$i-1]*(0.0141/100);
			}			
			$cuotaconseguros[$i]=$primadesgravamen[$i]+$cuotasinseguro+$primaseguro;
			$ctc[$i]=array_sum($cuotaconseguros);
			$cae[$i] = $cuotaconseguros[$i]*((1+($tasamensual))**$plazomeses)/(((1+($tasamensual))**$plazomeses)-1)/12;
			$caesum[$i] = array_sum($cae);

		}
		$cae = end($caesum)/100;
		return number_format($cae,2) . "%";
	}
	public function getCtc($plazo=0) {
		$tasamensual=$this->_tasamensual;
		$saldo[]=$this->_montoinicialuf;
		$cuotasinseguro=$this->_cuotasinseguro;
		$iteresmensual[]=0;
		$amortizacion[]=0;
		$cuotaconseguros[]=0;
		$primaseguro=$this->_primaseguro;
		$ctc=0;
		$plazomeses = $plazo?$plazo*12:$this->_plazo*12;
		for ($i = 1; $i <= $plazomeses; $i++) {
			$iteresmensual[$i]=$saldo[$i-1]*($tasamensual);
			$amortizacion[$i]=$cuotasinseguro-$iteresmensual[$i];
			$saldo[$i]=$saldo[$i-1]-$amortizacion[$i];
			if($i==1){
				$primadesgravamen[$i]=$saldo[$i-1]*(0.0141/100)*2;
			}else{
				$primadesgravamen[$i]=$saldo[$i-1]*(0.0141/100);
			}
			$cuotaconseguros[$i]=$primadesgravamen[$i]+$cuotasinseguro+$primaseguro;
			$ctc +=$cuotaconseguros[$i];
		}
		return $ctc;
	}
	public function getTermOption() {
		$terms = $this->termblock->getTermCollection();
		$options=array();
		foreach($terms as $key => $term){
			if($term->getOptionOne()==$this->_plazo){
				$options[]=$term->getOptionTwo();
				$options[]=$term->getOptionThree();
			}
		}
		return $options;
	}

	public function getTitle()
    {
        return $this->_helper->getHipotecaConfig('title');
    }

    public function getButtonText()
    {
        return $this->_helper->getHipotecaSuccessConfig('button_text');
    }

    public function getButtonLink()
    {
        return $this->_helper->getHipotecaSuccessConfig('button_link');
    }

    public function getLegalTerms()
    {
        return $this->_helper->getHipotecaConfig('legal_terms');
    }
	public function getBaseMediaDir()
    {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }
	public function getDate($format='Y-m-d H:i:s'){
		return $this->date->date(new \DateTime())->format($format);
	}
	public function floorDec($number, $precision, $separator)
	{
		$number_part=explode($separator, $number);
		$number_part[1]=substr_replace($number_part[1],$separator,$precision,0);
		if($number_part[0]>=0)
		{$number_part[1]=floor($number_part[1]);}
		else
		{$number_part[1]=ceil($number_part[1]);}
		
		$ceil_number= array($number_part[0],$number_part[1]);
		return implode($separator,$ceil_number);
	}
	public function getCaeTir($plazo=0) {
		$montos[]=-$this->_montoinicialuf;
		$tasamensual=$this->_tasamensual;
		$saldo[]=$this->_montoinicialuf;
		$cuotasinseguro=$this->_cuotasinseguro;
		$iteresmensual[]=0;
		$amortizacion[]=0;
		$cuotaconseguros[]=0;
		$primaseguro=$this->_primaseguro;
		$ctc=0;
		$plazomeses = $plazo?$plazo*12:$this->_plazo*12;
		for ($i = 1; $i <= $plazomeses; $i++) {
			$iteresmensual[$i]=$saldo[$i-1]*($tasamensual);
			$amortizacion[$i]=$cuotasinseguro-$iteresmensual[$i];
			$saldo[$i]=$saldo[$i-1]-$amortizacion[$i];
			if($i==1){
				$primadesgravamen[$i]=$saldo[$i-1]*(0.0141/100)*2;
			}else{
				$primadesgravamen[$i]=$saldo[$i-1]*(0.0141/100);
			}
			$montos[$i]=$primadesgravamen[$i]+$cuotasinseguro+$primaseguro;
		}
		$cae = $this->tir($montos);
		return number_format($cae,3) . "%";
	}
	public function vpl($tasa, $montos)
	{
		$ret= $montos[0];

		for ($i = 1; $i < count($montos); $i++) {
			$pow=pow((1.0 + $tasa),$i);
			if($pow == 0)
			$pow=0.000000001;
			$ret += $montos[$i] / $pow;
		}
		return $ret;
	}
	public function tir($montos)
	{	
		$ret = -1000000000.0;
		$juros_inicial = -1.0;
		$juros_medio = 0.0;
		$juros_final = 1.0;
		$vpl_inicial = 0.0;
		$vpl_final = 0.0;
		$vf = 0.0;
		$erro = 1e-5;
		
		for ($i=0; $i<100; $i++) {
			$vpl_inicial = $this->vpl($juros_inicial, $montos);
			$vpl_final = $this->vpl($juros_final, $montos);
		if ($this->sinal($vpl_inicial) != $this->sinal($vpl_final))
			break;
		$juros_inicial -= 1.0;
		$juros_final += 1.0;
		};
		$count = 0;
		for (;;) {
			$juros_medio = ($juros_inicial + $juros_final) / 2.0;        
			$vpl_medio = $this->vpl($juros_medio, $montos);
				
			if (abs($vpl_medio) <= $erro) {
				return $juros_medio*100.0*12;
			};
			if ($this->sinal($vpl_inicial) == $this->sinal($vpl_medio)) {
					$juros_inicial = $juros_medio;
				$vpl_inicial = $this->vpl($juros_medio, $montos);          
			} else {
					$juros_final = $juros_medio;
				$vpl_final = $this->vpl($juros_medio, $montos);          
			};
		};
		return $ret;
	}
	public function sinal($x) {
		return $x < 0.0 ? -1 : 1;
	}

	/**
     * getIsEnabled
     *
     * @return void
     */
    public function getIsEnabled()
	{
		return $this->crosseling->getGeneralConfig('hipotecario/enable');
	}

	/**
     * getCampaignType
     *
     * @return void
     */
    public function getCampaignType()
	{
		return $this->crosseling->getGeneralConfig('hipotecario/campaign_type');
	}

	/**
     * getDelayModal
     *
     * @return void
     */
    public function getDelayModal()
	{
		return $this->crosseling->getGeneralConfig('hipotecario/delay_modal');
	}
	
	/**
     * getCampaignTitle
     *
     * @return void
     */
    public function getCampaignTitle()
	{
		return $this->crosseling->getGeneralConfig('hipotecario/title');
	}

	/**
     * getCampaignDescription
     *
     * @return void
     */
    public function getCampaignDescription()
	{
		return $this->crosseling->getGeneralConfig('hipotecario/description');
	}

	/**
     * getCampaignColor
     *
     * @return void
     */
    public function getCampaignColor()
	{
		return $this->crosseling->getGeneralConfig('hipotecario/bg_color');
	}

	/**
     * getEnableButton
     *
     * @return void
     */
    public function getEnableButton()
	{
		return $this->crosseling->getGeneralConfig('hipotecario/enable_buttom');
	}

	/**
     * getButtonTextConfig
     *
     * @return void
     */
    public function getButtonTextConfig()
	{
		return $this->crosseling->getGeneralConfig('hipotecario/buttom_text');
	}

	/**
     * getButtonLinkConfig
     *
     * @return void
     */
    public function getButtonLinkConfig()
	{
		return $this->crosseling->getGeneralConfig('hipotecario/buttom_link');
	}

	/**
     * getButtonColor
     *
     * @return void
     */
    public function getButtonColor()
	{
		return $this->crosseling->getGeneralConfig('hipotecario/button_color');
	}

	/**
	 * Ruta imagen
	 */
	public function getImageConfig($key)
	{
		return $this->getMediaUrl(UrlInterface::URL_TYPE_MEDIA) . 
		'crosseling_campaigns/' .
		$this->crosseling->getGeneralConfig($key);
	}

}
