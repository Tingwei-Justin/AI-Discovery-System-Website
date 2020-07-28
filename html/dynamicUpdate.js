document.addEventListener('DOMContentLoaded', function ()
{
    document.getElementById("chooseButton1").addEventListener("click", function(){debugUpdateClusterData(1);});
    document.getElementById("chooseButton2").addEventListener("click", function(){debugUpdateClusterData(2);});
    document.getElementById("chooseButton3").addEventListener("click", function(){debugUpdateClusterData(3);});
});

function login()
{
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.open('GET','./login.php?id='+"login", true);
    xmlhttp.onreadystatechange=function()
    {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200)
        {
            if(xmlhttp.responseText != "nothing")
            {
                document.getElementById("user").innerHTML = xmlhttp.responseText;
                document.getElementById("login").innerHTML = "";
                document.getElementById("logoff").innerHTML = "<a href=\"destroy_session.php\" class=\"btn  btn-lg mybutton_cyano wow fadeIn\" data-wow-delay=\"0.1s\"><span class=\"network-name\">Log off</span></a>"
            }
            else
            {
                document.getElementById("login").innerHTML = "<a href=\"login.html\" class=\"btn  btn-lg mybutton_cyano wow fadeIn\" data-wow-delay=\"0.1s\"><span class=\"network-name\">Log in</span></a>"
                document.getElementById("logoff").innerHTML = "";
            }
        }
    };
    xmlhttp.send(null);
}
function showSelectedInfo()
{
    var xmlHttp = new XMLHttpRequest();
    var brand = $("#brand").val();
    var releaseDateFrom = document.getElementById("datefrom").value;
    var releaseDateTo = document.getElementById("dateto").value;
    var price_min = document.getElementById("price_min").value;
    var price_max = document.getElementById("price_max").value;
    var dataString = 'brand='+brand+'&releaseDateFrom='+releaseDateFrom+'&releaseDateTo='+releaseDateTo+'&price_min='+price_min+'&price_max='+price_max;

    var url = "customerSelectData.php?"+dataString;
    xmlHttp.onreadystatechange=function()
    {
        if (xmlHttp.readyState==4 && xmlHttp.status==200)
        {
            document.getElementById("showClusterData").innerHTML=xmlHttp.responseText;
        }
    }
    xmlHttp.open("GET",url,true);
    xmlHttp.send(null);
}

function showClusterData()
{
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.open('GET','./clusterData.php', true);
    xmlhttp.onreadystatechange=function()
    {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200)
                document.getElementById("showClusterData").innerHTML = xmlhttp.responseText;
    };
    xmlhttp.send(null);
}

function debugUpdateClusterData(id)
{
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.open('GET','./debugClusterData.php?id='+id, true);
    xmlhttp.onreadystatechange=function()
    {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200)
            document.getElementById("showClusterData").innerHTML = xmlhttp.responseText;
    };
    xmlhttp.send(null);
}

function updateClusterData(id)
{

    var name = "choiceDetail"+id.toString();
    var element = document.getElementById(name).innerText;
    //element = element.toString();
    //alert(element);
    var index1 = element.indexOf("Final");
    if(index1 !== -1)
        window.open('https://www.google.com/search?q=' + element.replace('Final: ',''),'_blank','width=800,height=500,menubar=no,toolbar=no, status=no,scrollbars=yes');
    else
    {
        document.getElementById("test").innerHTML = "";

        var xmlhttp = new XMLHttpRequest();
        xmlhttp.open('GET','./clusterData.php?id='+id, true);
        xmlhttp.onreadystatechange=function()
        {
            if (xmlhttp.readyState == 4 && xmlhttp.status == 200 && xmlhttp.responseText != "")
            {
                var array = xmlhttp.responseText.split(',');
                for(var i=0; i<3; i++)
                {
                    if(array[i] === undefined)
                    {
                        array[i] = "Nothing        ";
                        document.getElementById("button-detail"+(i+1).toString()).innerHTML = "";
                    }
                    if(array[i].indexOf("Final")!==-1)
                    {
                        document.getElementById("button-choose"+(i+1).toString()).innerHTML = "Google!";
                        document.getElementById("test").innerHTML = "<br><h4>Process finished! Search google to learn more about your item</h4>";
                    }

                }
                document.getElementById("choiceDetail1").innerHTML = array[0].substring(0, array[0].length - 8);
                document.getElementById("choiceDetail2").innerHTML = array[1].substring(0, array[1].length - 8);
                document.getElementById("choiceDetail3").innerHTML = array[2].substring(0, array[2].length - 8);

                for(var i=0; i<3; i++)
                {
                    if(array[i].indexOf("Noth")!=-1)
                        array[i] = "stop cross";
                }
                retrieveGoogleImage(array[0],'picture1');
                retrieveGoogleImage(array[1],'picture2');
                retrieveGoogleImage(array[2],'picture3');
            }
        };
        xmlhttp.send(null);
    }
}

function showItemDetail(id)
{
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.open('GET','./ItemDetail.php?id='+id, true);
    xmlhttp.onreadystatechange=function()
    {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200)
                document.getElementById("test").innerHTML = xmlhttp.responseText;
    };
    xmlhttp.send(null);
}
function findItem(item)
{
    document.getElementById("test").innerHTML = "";
    document.getElementById("item_show").innerHTML = "<div class=\"col-sm-4  wow fadeInDown text-center\">\n" +
        "\t\t\t\t\t<span id=\"picture1\"><img  class=\"rotate\" src=\"img/icon/item0.svg\" height= 120px; alt=\"Generic placeholder image\"></span>\n" +
        "\n" +
        "\t\t\t\t\t<h3 class=\"name\" id=\"choiceDetail1\">Waiting...</h3>\n" +
        "\t\t\t\t\t<p class=\"lead\" ></p>\n" +
        "\t\t\t\t\t<p id=\"button-detail1\"><a class=\"btn btn-embossed btn-primary view\" role=\"button\" onclick=\"updateClusterData(1)\"><span id=\"button-choose1\">Choose!</span></a>\n" +
        "\t\t\t\t\t\t<a class=\"btn btn-embossed btn-info view\" role=\"button\" onclick=\"showItemDetail(1)\">Detail</a></p>\n" +
        "\t\t\t\t</div><!-- /.col-lg-4 -->\n" +
        "\n" +
        "\t\t\t\t<div class=\"col-sm-4 wow fadeInDown text-center\">\n" +
        "\t\t\t\t\t<span id=\"picture2\"><img  class=\"rotate\" src=\"img/icon/item0.svg\" height= 120px; alt=\"Generic placeholder image\"></span>\n" +
        "\t\t\t\t\t<h3 class=\"name\" id=\"choiceDetail2\">Waiting...</h3>\n" +
        "\t\t\t\t\t<p class=\"lead\" ></p>\n" +
        "\t\t\t\t\t<p id=\"button-detail2\"><a class=\"btn btn-embossed btn-primary view\" role=\"button\" onclick=\"updateClusterData(2)\"><span id=\"button-choose2\">Choose!</span></a>\n" +
        "\t\t\t\t\t\t<a class=\"btn btn-embossed btn-info view\" role=\"button\" onclick=\"showItemDetail(2)\">Detail</a></p>\n" +
        "\t\t\t\t</div><!-- /.col-lg-4 -->\n" +
        "\n" +
        "\t\t\t\t<div class=\"col-sm-4 wow fadeInDown text-center\">\n" +
        "\t\t\t\t\t<span id=\"picture3\"><img  class=\"rotate\" src=\"img/icon/item0.svg\" height= 120px; alt=\"Generic placeholder image\"></span>\n" +
        "\t\t\t\t\t<h3 class=\"name\" id=\"choiceDetail3\">Waiting...</h3>\n" +
        "\t\t\t\t\t<p class=\"lead\" ></p>\n" +
        "\t\t\t\t\t<p id=\"button-detail3\"><a class=\"btn btn-embossed btn-primary view\" role=\"button\" onclick=\"updateClusterData(3)\"><span id=\"button-choose3\">Choose!</span></a>\n" +
        "\t\t\t\t\t\t<a class=\"btn btn-embossed btn-info view\" role=\"button\" onclick=\"showItemDetail(3)\">Detail</a></p>\n" +
        "\t\t\t\t</div>" +
        "<div class='text-center'><br><a class=\"btn btn-embossed btn-info view\" role=\"button\" onclick=\"showItemDetail(4)\">Compare All</a></div>";
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.open('GET','./selectData.php?item='+item, true);

    xmlhttp.onreadystatechange=function()
    {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200)
        {
            var array = xmlhttp.responseText.split(',');

            document.getElementById("choiceDetail1").innerHTML = array[0].substring(0, array[0].length - 8);
            document.getElementById("choiceDetail2").innerHTML = array[1].substring(0, array[1].length - 8);
            document.getElementById("choiceDetail3").innerHTML = array[2].substring(0, array[2].length - 8);
            retrieveGoogleImage(array[0],'picture1');
            retrieveGoogleImage(array[1],'picture2');
            retrieveGoogleImage(array[2],'picture3');
           // document.getElementById("test").innerHTML = xmlhttp.responseText;
        }
    };
    xmlhttp.send(null);

}
function destroySession()
{
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.open('GET','./destroy_session.php', true);
    xmlhttp.onreadystatechange=function()
    {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200)
        {
            alert(xmlhttp.responseText);
            window.location.href="index.html";
        }
    };
    xmlhttp.send(null);
}


function getXMLHttpObject()
{
    var xmlHttp=null;
    try
    {
        // Firefox, Opera 8.0+, Safari
        xmlHttp=new XMLHttpRequest();
    }
    catch (e)
    {
        // Internet Explorer
        try
        {
            xmlHttp=new ActiveXObject("Msxml2.XMLHTTP");
        }
        catch (e)
        {
            xmlHttp=new ActiveXObject("Microsoft.XMLHTTP");
        }
    }
    return xmlHttp;
}