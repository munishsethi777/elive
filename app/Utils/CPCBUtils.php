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
    
    
    public static function getNameEmailByMobileNumber($mobileNumber){
        $arr = array();
        //BHOOMI
        $arr['9927977001'] = array("name"=>"Mr. V.S Sharma ","email"=>"vssharma49@gmail.com","department"=>"Industry");
        $arr['9927008103'] = array("name"=>"Mr. V.S Sharma ","email"=>"vssharma49@gmail.com","department"=>"Industry");
        $arr['9993702598'] = array("name"=>"Prakash Malviya","email"=>"malviya.prakash4@gmail.com","department"=>"Industry");
        $arr['7574800191'] = array("name"=>"Jitendra Kumar Behera","email"=>"beheraj@coromandel.murugappa.com","department"=>"Industry");
        $arr['8818898793'] = array("name"=>"Alok Mosanty","email"=>"gopal.purchase2006@gmail.com","department"=>"Industry");
        $arr['9584710000'] = array("name"=>"Ankit Agarwal","email"=>"indospongepower@gmail.com","department"=>"Industry");
        $arr['9993250309'] = array("name"=>"Narendra Dixena","email"=>"jc@mahendragroup.net","department"=>"Industry");
        $arr['9351468221'] = array("name"=>"VK Mehtha","email"=>"manish.yadav@mangalamcement.com","department"=>"Industry");
        $arr['9415030422'] = array("name"=>"Sh. Ram Gopal","email"=>"roaligarh@uppcb.com","department"=>"SPCB");
        $arr['9351468221'] = array("name"=>"VK Mehtha","email"=>"env@mangalamcement.com","department"=>"Industry");
        $arr['7412042548'] = array("name"=>"VK Mehtha","email"=>"env@mangalamcement.com","department"=>"Industry");
        $arr['9414801375'] = array("name"=>"Sunil Aggarwal","email"=>"sunilaggarwal@ramky.com","department"=>"Industry");
        $arr['9814807126'] = array("name"=>"Madan Gopal","email"=>"madan.gopal@ramky.com","department"=>"Industry");
        $arr['7488351796'] = array("name"=>"Pradeep kumar Jha","email"=>"head.jharkhand@ramky.com","department"=>"Industry");
        $arr['7899846300'] = array("name"=>"Pushpendra singh","email"=>"pushpendrasingh.c@ramky.com","department"=>"Industry");
        $arr['9971693775'] = array("name"=>"Sanjay Prakash Garg","email"=>"sanjayprakash.garg@ramky.com","department"=>"Industry");
        $arr['9990603358'] = array("name"=>"Sh. Utsav Sharma","email"=>"roghaziabad@uppcb.com","department"=>"SPCB");
        $arr['7781012211'] = array("name"=>"Rajeev Kumar","email"=>"rajeev.kumar@ramky.com","department"=>"Industry");
        $arr['9431818356'] = array("name"=>"Dr. Suneel Kumar Jha","email"=>"ddc-muzaffarpur-bih@nic.in","department"=>"SPCB");
        $arr['9324627400'] = array("name"=>"Satish Kumar","email"=>"incinmwml@ramky.com","department"=>"Industry");
        $arr['7989304424'] = array("name"=>"Satish Kumar","email"=>"incinmwml@ramky.com","department"=>"Industry");
        $arr['8720041011'] = array("name"=>"Bhaskar Turkar","email"=>"brturkar@nirosispat.com,","department"=>"Industry");
        $arr['8720041083'] = array("name"=>"Satish Sahu","email"=>"shekhar@nirosispat.com","department"=>"Industry");
        $arr['9109100637'] = array("name"=>"Girish Lodhi","email"=>"philsiltara@gmail.com","department"=>"Industry");
        $arr['9109100637'] = array("name"=>"Girish Lodhi","email"=>"philpurchase.siltara@gmail.com","department"=>"Industry");
        $arr['8818898793'] = array("name"=>"Girish Lodhi","email"=>"philsiltara@gmail.com","department"=>"Industry");
        $arr['9182369916'] = array("name"=>"Vijayakumar. M","email"=>"inst-unit1@natems.com","department"=>"Industry");
        $arr['7760714743'] = array("name"=>"PRASHANTH NAYAK MULKI","email"=>"reaelmngl@ramky.com","department"=>"Industry");
        $arr['9039199088'] = array("name"=>"S Biswas","email"=>"bsp@sksl.in","department"=>"Industry");
        $arr['9596774810'] = array("name"=>"Bipin Choudhary","email"=>"bipinsatyam2009.s@rediffmail.com","department"=>"Industry");
        $arr['9906087519'] = array("name"=>"Bipin Choudhary","email"=>"satyam_cements@yahoo.com","department"=>"Industry");
        $arr['9977285015'] = array("name"=>"Prashanth","email"=>"shktechnical@gmail.com","department"=>"Industry");
        $arr['9977285005'] = array("name"=>"Prashanth","email"=>"shkraipur@gmail.com","department"=>"Industry");
        $arr['9906202775'] = array("name"=>"Sukhdev Singh","email"=>"shriramindustries14@gmail.com","department"=>"Industry");
        $arr['9617770676'] = array("name"=>"Manoj Mahajan","email"=>"manoj.mahajan@topworthgroup.com","department"=>"Industry");
        $arr['9552524860'] = array("name"=>"Prakash Niwrutirao Munde","email"=>"prakashnmunde@gmail.com","department"=>"Industry");
        $arr['9623907644'] = array("name"=>"VS Pangri","email"=>"vsskpangri@yahoo.co.in","department"=>"Industry");
        $arr['6289217215'] = array("name"=>"Nabarun Ghosh","email"=>"opshead.kalyani@ramky.com","department"=>"Industry");
        $arr['9617770676'] = array("name"=>"Manoj Mahajan","email"=>"manoj.mahajan@topworthgroup.com","department"=>"Industry");
        
        //CPCB
        $arr['9891436656'] = array("name"=>"Sh. Ankur Tiwari","email"=>"n.a","department"=>"CPCB");
        $arr['9911706629'] = array("name"=>"Sh. Ankur","email"=>"ankur.cpcb@nic.in","department"=>"CPCB");
        $arr['9868143169'] = array("name"=>"Sh. D. Gouda","email"=>"dinabandhu.cpcb@nic.in","department"=>"CPCB");
        $arr['9868210860'] = array("name"=>"Sh Ajay Aggarwal","email"=>"ajayaggarwal.cpcb@nic.in","department"=>"CPCB");
        $arr['9968276538'] = array("name"=>"Sh PK Gupta","email"=>"pkgupta@cpcb.nic.in","department"=>"CPCB");
        $arr['7840015561'] = array("name"=>"Sh. Gurnam Singh","email"=>"dye.cpcb@nic.in","department"=>"CPCB");
        $arr['9911607156'] = array("name"=>"Sh. Ashbir Singh","email"=>"ashbirsingh@gmail.com","department"=>"CPCB");
        $arr['9711113945'] = array("name"=>"Sh. S.K. Paliwal","email"=>"sanjeevpaliwal.cpcb@nic.in","department"=>"CPCB");
        $arr['9871177730'] = array("name"=>"Mr. A.K. Vidyarthi","email"=>"vidyarthi.cpcb@nic.in","department"=>"CPCB");
        $arr['8745057374'] = array("name"=>"Dr.M. Madhusudanan","email"=>"mmadhusudanan.cpcb@nic.in","department"=>"CPCB");
        $arr['9312305450'] = array("name"=>"Mr. Abhey Singh","email"=>"n.a","department"=>"CPCB");
        $arr['8745057374'] = array("name"=>"Dr.M. Madhusudanan","email"=>"mmadhusudanan.cpcb@nic.in","department"=>"CPCB");
        $arr['8745073839'] = array("name"=>"CETP","email"=>"cetp.smsalertpc7@gmail.com","department"=>"CPCB");
        $arr['9891712277'] = array("name"=>"Mrs. Dipti Kapil","email"=>"deepti.cpcb@nic.in","department"=>"CPCB");
        $arr['9818657053'] = array("name"=>"CHWI","email"=>"n.a","department"=>"CPCB");
        $arr['783989179'] = array("name"=>"n.a","email"=>"n.a","department"=>"CPCB");
        $arr['9450073910'] = array("name"=>"Mr. A.K. Anand","email"=>"n.a","department"=>"CPCB");
        $arr['7678997444'] = array("name"=>"Sh SK Gupta","email"=>"skgupta110@yahoo.com","department"=>"CPCB");
        $arr['9411232637'] = array("name"=>"Mr Nazim","email"=>"nazim.cpcb@nic.in","department"=>"CPCB");
        $arr['9868206558'] = array("name"=>"Mr NK Gupta","email"=>"nkgupta.cpcb@nic.in","department"=>"CPCB");
        $arr['9990300758'] = array("name"=>"Sh. Vivek K.","email"=>"distillery.cpcb@nic.in","department"=>"CPCB");
        $arr['8376931053'] = array("name"=>"Mrs. Reena Satavan","email"=>"reenasatawan.cpcb@nic.in","department"=>"CPCB");
        $arr['9718209083'] = array("name"=>"Sh. Kamlesh Singh","email"=>"ks.cpcb@nic.in","department"=>"CPCB");
        $arr['9695206868'] = array("name"=>"Shri P K Mishra","email"=>"mishrapkin@yahoo.com","department"=>"CPCB");
        
        
        
        if(isset($arr[$mobileNumber])){
            return $arr[$mobileNumber];
        }
        return null;
    }
  }
?>