/**
 * 设置内容body高度最少是屏幕高度
 * @type {jQuery}
 */
/*var wHeight=$(window).height();
var body=$(".container .body");
bHeight=body.height();
console.log(wHeight,bHeight);
if(bHeight<wHeight){
    body.css("height",(wHeight-80)+'px');
}*/

/**
 * 用户操作（退出，写作）
 */
var opt=$(".header .user .opt");
var userPopover =$("#userPopover");
if(opt.length>0 && userPopover.length>0){
    var offsetLeft = opt.offset().left;
    var offsetTop = opt.offset().top;

    userPopover.css("left",(offsetLeft-userPopover.width()/2+opt.width()/2)+"px");
    userPopover.css("top",(offsetTop+opt.height())+"px");

    opt.click(function () {
        if(userPopover.css("display")=="none"){
            userPopover.get(0).style.display="block";
        } else{
            userPopover.get(0).style.display="none";
        }
        return false;
    });

    opt.hover(function () {
        $(this).css("color","#555");
    },function () {
        $(this).css("color","#333");
    });

    $(document).click(function () {
        if(userPopover.css("display")=="block"){
            userPopover.get(0).style.display="none";
        }
    });
}



