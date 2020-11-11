<?php
  class CPCBUtils{

    public static function getSMSNumbersByIndustry($industryCategory){
       $arr = array();
        $arr['Aluminium'] = "9891436656";//,9868210860,9871460333,9411232637";
        $arr['Cement'] = "9911706629";//9911706629,9411232637";
       $arr['Chlor Alkali'] = "9868143169";
        $arr['Copper'] = "9891436656";//9868210860,9871460333,9411232637";
       $arr['Dairy'] = "9868210860";
       $arr['Distillery'] = "9968276538,9990300758";
        $arr['Dye & Dye Intermediates'] = "7840015561,9911607156";
        $arr['Fertilizer'] = "7840015561,9911607156";
        $arr['Iron & Steel'] = "9411232637";//9411232637";
       $arr['Oil Refinery'] = "9868143169";
        $arr['Pesticide'] = "7840015561,9911607156";
       $arr['Petrochemicals'] = "9868143169";
       $arr['Pharmaceuticals'] = "9868143169";
        $arr['Power Plant'] = "9711113945";//9711113945,9411232637";
       $arr['Pulp & Paper'] = "9871177730,9990300758";
       $arr['Sugar'] = "8745057374,9990300758";
       $arr['Tannery'] = "9312305450,9868210860";
        $arr['Zinc'] = "9891436656";//9868210860,9871460333,9411232637";
       $arr['Textile'] = "8745057374,9990300758";
       $arr['Food & Beverages'] = "8745057374";
       $arr['CETP'] = "8745073839";//9411232637,9968276538,
       $arr['Slaughter House'] = "8745057374,9868210860";
       $arr['Commom Hazardous Waste Incinerator'] = "9891712277,9818657053";
       $arr['Biomedical Waste Incineration'] = "";
        $arr['Ganga Basin Industries'] = "7839891792,9450073910";
       //$arr['Power Plant'] = "9417265865";
       return $arr[$industryCategory];
    }

    public static function getEmailsByIndustry($industryCategory){
        $arr = array();
        $arr['Aluminium'] = "aluminium.cpcb@nic.in";//ajayaggarwal.cpcb@nic.in,rajeshdebroy.cpcb@nic.in;
        $arr['Cement'] = "ankur.cpcb@nic.in,cement.cpcb@nic.in";
        $arr['Chlor Alkali'] = "dinabandhu.cpcb@nic.in,chloralkali.cpcb@nic.in";
        $arr['Copper'] = "copper.cpcb@nic.in";//ajayaggarwal.cpcb@nic.in,rajeshdebroy.cpcb@nic.in;
        $arr['CETP'] = "nazim.cpcb@nic.in,cetp.cpcb@nic.in,cetp.smsalertipc7@gmail.com,ipc7.cpcb@gov.in";
        $arr['Commom Hazardous Waste Incinerator'] = "deepti.cpcb@nic.in";
        $arr['Common Integrated Treatment, Storage & Disposal Facilities'] = "tsdf.cpcb@nic.in";
        $arr['Biomedical Waste Incineration'] = "biomedical.cpcb@nic.in";
        $arr['Dairy'] = "ajayaggarwal.cpcb@nic.in";
        $arr['Distillery'] = "pkgupta.cpcb@nic.in,vivekpcb@gmail.com,distillery.cpcb@nic.in";
        $arr['Dye & Dye Intermediates'] = "ashbirsingh@gmail.com,dye.cpcb@nic.in";
        $arr['Fertilizer'] = "ashbirsingh@gmail.com,fertilizer.cpcb@nic.in";
        $arr['Food & Beverages'] = "mmadhusudanan.cpcb@nic.in";
        $arr['Iron & Steel'] = "nazim.cpcb@nic.in";
        $arr['Oil Refinery'] = "dinabandhu.cpcb@nic.in,oilrefinery.cpcb@nic.in";
        $arr['Pesticide'] = "ashbirsingh@gmail.com,pesticide.cpcb@nic.in";
        $arr['Petrochemicals'] = "dinabandhu.cpcb@nic.in,petrochemicals.cpcb@nic.in";
        $arr['Pharmaceuticals'] = "dinabandhu.cpcb@nic.in,pharma.cpcb@nic.in";
        $arr['Power Plant'] = "sanjeevpaliwal.cpcb@nic.in,power.cpcb@nic.in";//sanjeevpaliwal.cpcb@nic.in;
        $arr['Pulp & Paper'] = "vidyarthi.cpcb@nic.in,ks.cpcb@nic.in,paper.cpcb@nic.in";
        $arr['Slaughter House'] = "mmadhusudanan.cpcb@nic.in,ajayaggarwal.cpcb@nic.in";
        $arr['Sugar'] = "reenasatawan.cpcb@nic.in,sugar.cpcb@nic.in";
        $arr['Textile'] = "";
        $arr['Tannery'] = "nazim.cpcb@nic.in,tannery.cpcb@nic.in,ajayaggarwal.cpcb@nic.in";
        $arr['Zinc'] = "zinc.cpcb@nic.in";//ajayaggarwal.cpcb@nic.in,rajeshdebroy.cpcb@nic.in;
        // $arr['Grossly Polluting Industries'] = "gpi1.cpcb@nic.in";
        // $arr['Ganga Basin Industries'] = "ngrba1.cpcb@nic.in";
        //$arr['Power Plant'] = "baljeetgaheer@gmail.com"; 
        return $arr[$industryCategory];
    }
	public static function getReplyEmailsByIndustry($industryCategory){
        $arr = array();
        $arr['Aluminium'] = "aluminium.cpcb@nic.in";
        $arr['Cement'] = "cement.cpcb@nic.in";
        $arr['Chlor Alkali'] = "chloralkali.cpcb@nic.in";
        $arr['Copper'] = "copper.cpcb@nic.in";
        $arr['Distillery'] = "distillery.cpcb@nic.in";
        $arr['Dye & Dye Intermediates'] = "dye.cpcb@nic.in";
        $arr['Fertilizer'] = "fertilizer.cpcb@nic.in";
        $arr['Iron & Steel'] = "steel.cpcb@nic.in";
        $arr['Oil Refinery'] = "oilrefinery.cpcb@nic.in";
        $arr['Pesticide'] = "pesticide.cpcb@nic.in";
        $arr['Petrochemicals'] = "petrochemical.cpcb@nic.in";
        $arr['Pharmaceuticals'] = "pharma.cpcb@nic.in";
        $arr['Power Plant'] = "power.cpcb@nic.in";
        $arr['Pulp & Paper'] = "paper.cpcb@nic.in";
        $arr['Sugar'] = "sugar.cpcb@nic.in";
        $arr['Tannery'] = "tannery.cpcb@nic.in";
        $arr['Zinc'] = "zinc.cpcb@nic.in";
        //$arr['Textile'] = "mmadhusudanan.cpcb@nic.in";
        //$arr['Food & Beverages'] = "mmadhusudanan.cpcb@nic.in";
        $arr['CETP'] = "cetp.cpcb@nic.in";
        //$arr['Slaughter House'] = "mmadhusudanan.cpcb@nic.in";        
        $arr['Commom Hazardous Waste Incinerator'] = "tsdf.cpcb@nic.in";
		$arr['Biomedical Waste Incineration'] = "biomedical.cpcb@nic.in";
		$arr['Common Integrated Treatment, Storage & Disposal Facilities'] = "tsdf.cpcb@nic.in";
        $arr['Grossly Polluting Industries'] = "gpi1.cpcb@nic.in";
        $arr['Ganga Basin Industries'] = "ngrba1.cpcb@nic.in ";
        return $arr[$industryCategory];
    }
  }
?>
