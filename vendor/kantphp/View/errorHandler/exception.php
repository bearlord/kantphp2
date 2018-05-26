<?php
/* @var $this \yii\web\View */
/* @var $exception \Exception */
/* @var $handler \yii\web\ErrorHandler */
?>
<?php if (method_exists($this, 'beginPage')) $this->beginPage(); ?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8"/>

    <title><?php
        $name = $handler->getExceptionName($exception);
        if ($exception instanceof \Kant\Exception\HttpException) {
            echo (int) $exception->statusCode . ' ' . $handler->htmlEncode($name);
        } else {
            $name = $handler->getExceptionName($exception);
            if ($name !== null) {
                echo $handler->htmlEncode($name . ' – ' . get_class($exception));
            } else {
                echo $handler->htmlEncode(get_class($exception));
            }
        }
    ?></title>

    <style type="text/css">
/* reset */
html,body,div,span,h1,h2,h3,h4,h5,h6,p,pre,a,code,em,img,strong,b,i,ul,li{
    margin: 0;
    padding: 0;
    border: 0;
    font-size: 100%;
    font: inherit;
    vertical-align: baseline;
}
body{
    line-height: 1;
}
ul{
    list-style: none;
}

/* base */
a{
    text-decoration: none;
}
a:hover{
    text-decoration: underline;
}
h1,h2,h3,p,img,ul li{
    font-family: Arial,sans-serif;
    color: #505050;
}
/*corresponds to min-width of 860px for some elements (.header .footer .element ...)*/
@media screen and (min-width: 960px) {
    html,body{
        overflow-x: hidden;
    }
}

/* header */
.header{
    min-width: 860px; /* 960px - 50px * 2 */
    margin: 0 auto;
    background: #f3f3f3;
    padding: 40px 50px 30px 50px;
    border-bottom: #ccc 1px solid;
}
.header h1{
    font-size: 30px;
    color: #e57373;
    margin-bottom: 30px;
}
.header h1 span, .header h1 span a{
    color: #e51717;
}
.header h1 a{
    color: #e57373;
}
.header h1 a:hover{
    color: #e51717;
}
.header img{
    float: right;
    margin-top: -15px;
}
.header h2{
    font-size: 20px;
    line-height: 1.25;
}
.header pre{
    margin: 10px 0;
    overflow-y: scroll;
    font-family: Courier, monospace;
    font-size: 14px;
}

/* previous exceptions */
.header .previous{
    margin: 20px 0;
    padding-left: 30px;
}
.header .previous div{
    margin: 20px 0;
}
.header .previous .arrow{
    -moz-transform: scale(-1, 1);
    -webkit-transform: scale(-1, 1);
    -o-transform: scale(-1, 1);
    transform: scale(-1, 1);
    filter: progid:DXImageTransform.Microsoft.BasicImage(mirror=1);
    font-size: 26px;
    position: absolute;
    margin-top: -3px;
    margin-left: -30px;
    color: #e51717;
}
.header .previous h2{
    font-size: 20px;
    color: #e57373;
    margin-bottom: 10px;
}
.header .previous h2 span{
    color: #e51717;
}
.header .previous h2 a{
    color: #e57373;
}
.header .previous h2 a:hover{
    color: #e51717;
}
.header .previous h3{
    font-size: 14px;
    margin: 10px 0;
}
.header .previous p{
    font-size: 14px;
    color: #aaa;
}
.header .previous pre{
    font-family: Courier, monospace;
    font-size: 14px;
    margin: 10px 0;
}

/* call stack */
.call-stack{
    margin-top: 30px;
    margin-bottom: 40px;
}
.call-stack ul li{
    margin: 1px 0;
}
.call-stack ul li .element-wrap{
    cursor: pointer;
    padding: 15px 0;
    background-color: #fdfdfd;
}
.call-stack ul li.application .element-wrap{
    background-color: #fafafa;
}
.call-stack ul li .element-wrap:hover{
    background-color: #edf9ff;
}
.call-stack ul li .element{
    min-width: 860px; /* 960px - 50px * 2 */
    margin: 0 auto;
    padding: 0 50px;
    position: relative;
}
.call-stack ul li a{
    color: #505050;
}
.call-stack ul li a:hover{
    color: #000;
}
.call-stack ul li .item-number{
    width: 45px;
    display: inline-block;
}
.call-stack ul li .text{
    color: #aaa;
}
.call-stack ul li.application .text{
    color: #505050;
}
.call-stack ul li .at{
    float: right;
    display: inline-block;
    width: 7em;
    padding-left: 1em;
    text-align: left;
    color: #aaa;
}
.call-stack ul li.application .at{
    color: #505050;
}
.call-stack ul li .line{
    display: inline-block;
    width: 3em;
    text-align: right;
}
.call-stack ul li .code-wrap{
    display: none;
    position: relative;
}
.call-stack ul li.application .code-wrap{
    display: block;
}
.call-stack ul li .error-line,
.call-stack ul li .hover-line{
    background-color: #ffebeb;
    position: absolute;
    width: 100%;
    z-index: 100;
    margin-top: 0;
}
.call-stack ul li .hover-line{
    background: none;
}
.call-stack ul li .hover-line.hover,
.call-stack ul li .hover-line:hover{
    background: #edf9ff !important;
}
.call-stack ul li .code{
    min-width: 860px; /* 960px - 50px * 2 */
    margin: 15px auto;
    padding: 0 50px;
    position: relative;
}
.call-stack ul li .code .lines-item{
    position: absolute;
    z-index: 200;
    display: block;
    width: 25px;
    text-align: right;
    color: #aaa;
    line-height: 20px;
    font-size: 12px;
    margin-top: 1px;
    font-family: Consolas, monospace;
}
.call-stack ul li .code pre{
    position: relative;
    z-index: 200;
    left: 50px;
    line-height: 20px;
    font-size: 12px;
    font-family: Consolas, monospace;
    display: inline;
}
@-moz-document url-prefix() {
    .call-stack ul li .code pre{
        line-height: 20px;
    }
}

/* request */
.request{
    background-color: #fafafa;
    padding-top: 40px;
    padding-bottom: 40px;
    margin-top: 40px;
    margin-bottom: 1px;
}
.request .code{
    min-width: 860px; /* 960px - 50px * 2 */
    margin: 0 auto;
    padding: 15px 50px;
}
.request .code pre{
    font-size: 14px;
    line-height: 18px;
    font-family: Consolas, monospace;
    display: inline;
    word-wrap: break-word;
}

/* footer */
.footer{
    position: relative;
    height: 222px;
    min-width: 860px; /* 960px - 50px * 2 */
    padding: 0 50px;
    margin: 1px auto 0 auto;
}
.footer p{
    font-size: 16px;
    padding-bottom: 10px;
}
.footer p a{
    color: #505050;
}
.footer p a:hover{
    color: #000;
}
.footer .timestamp{
    font-size: 14px;
    padding-top: 67px;
    margin-bottom: 28px;
}
.footer img{
    position: absolute;
    right: -50px;
}

/* highlight.js */
.comment{
    color: #808080;
    font-style: italic;
}
.keyword{
    color: #000080;
}
.number{
    color: #00a;
}
.number{
    font-weight: normal;
}
.string, .value{
    color: #0a0;
}
.symbol, .char {
    color: #505050;
    background: #d0eded;
    font-style: italic;
}
.phpdoc{
    text-decoration: underline;
}
.variable{
    color: #a00;
}

body pre {
    pointer-events: none;
}
body.mousedown pre {
    pointer-events: auto;
}
    </style>
</head>

<body>
    <div class="header">
        <?php if ($exception instanceof \Kant\Exception\ErrorException): ?>

            <h1>
                <span><?= $handler->htmlEncode($exception->getName()) ?></span>
                &ndash; <?= $handler->addTypeLinks(get_class($exception)) ?>
            </h1>
        <?php else: ?>
            <h1><?php
                if ($exception instanceof \Kant\Web\HttpException) {
                    echo '<span>' . $handler->createHttpStatusLink($exception->statusCode, $handler->htmlEncode($exception->getName())) . '</span>';
                    echo ' &ndash; ' . $handler->addTypeLinks(get_class($exception));
                } else {
                    $name = $handler->getExceptionName($exception);
                    if ($name !== null) {
                        echo '<span>' . $handler->htmlEncode($name) . '</span>';
                        echo ' &ndash; ' . $handler->addTypeLinks(get_class($exception));
                    } else {
                        echo '<span>' . $handler->htmlEncode(get_class($exception)) . '</span>';
                    }
                }
            ?></h1>
        <?php endif; ?>
        <h2><?= nl2br($handler->htmlEncode($exception->getMessage())) ?></h2>

        <?php if ($exception instanceof \Kant\Database\Exception && !empty($exception->errorInfo)) {
            echo '<pre>Error Info: ' . print_r($exception->errorInfo, true) . '</pre>';
        } ?>

        <?= $handler->renderPreviousExceptions($exception) ?>
    </div>

    <div class="call-stack">
        <?= $handler->renderCallStack($exception) ?>
    </div>

    <div class="request">
        <div class="code">
            <?= $handler->renderRequest() ?>
        </div>
    </div>

    <div class="footer">
        <p class="timestamp"><?= date('Y-m-d, H:i:s') ?></p>
        <p><?= $handler->createServerInformationLink() ?></p>
        <p><a href="https://www.kantphp.com/">KantPHP Framework</a>/<?= $handler->createFrameworkVersionLink() ?></p>
    </div>

    <script type="text/javascript">
var hljs=new function(){function l(o){return o.replace(/&/gm,"&amp;").replace(/</gm,"&lt;").replace(/>/gm,"&gt;")}function b(p){for(var o=p.firstChild;o;o=o.nextSibling){if(o.nodeName=="CODE"){return o}if(!(o.nodeType==3&&o.nodeValue.match(/\s+/))){break}}}function h(p,o){return Array.prototype.map.call(p.childNodes,function(q){if(q.nodeType==3){return o?q.nodeValue.replace(/\n/g,""):q.nodeValue}if(q.nodeName=="BR"){return"\n"}return h(q,o)}).join("")}function a(q){var p=(q.className+" "+q.parentNode.className).split(/\s+/);p=p.map(function(r){return r.replace(/^language-/,"")});for(var o=0;o<p.length;o++){if(e[p[o]]||p[o]=="no-highlight"){return p[o]}}}function c(q){var o=[];(function p(r,s){for(var t=r.firstChild;t;t=t.nextSibling){if(t.nodeType==3){s+=t.nodeValue.length}else{if(t.nodeName=="BR"){s+=1}else{if(t.nodeType==1){o.push({event:"start",offset:s,node:t});s=p(t,s);o.push({event:"stop",offset:s,node:t})}}}}return s})(q,0);return o}function j(x,v,w){var p=0;var y="";var r=[];function t(){if(x.length&&v.length){if(x[0].offset!=v[0].offset){return(x[0].offset<v[0].offset)?x:v}else{return v[0].event=="start"?x:v}}else{return x.length?x:v}}function s(A){function z(B){return" "+B.nodeName+'="'+l(B.value)+'"'}return"<"+A.nodeName+Array.prototype.map.call(A.attributes,z).join("")+">"}while(x.length||v.length){var u=t().splice(0,1)[0];y+=l(w.substr(p,u.offset-p));p=u.offset;if(u.event=="start"){y+=s(u.node);r.push(u.node)}else{if(u.event=="stop"){var o,q=r.length;do{q--;o=r[q];y+=("</"+o.nodeName.toLowerCase()+">")}while(o!=u.node);r.splice(q,1);while(q<r.length){y+=s(r[q]);q++}}}}return y+l(w.substr(p))}function f(q){function o(s,r){return RegExp(s,"m"+(q.cI?"i":"")+(r?"g":""))}function p(y,w){if(y.compiled){return}y.compiled=true;var s=[];if(y.k){var r={};function z(A,t){t.split(" ").forEach(function(B){var C=B.split("|");r[C[0]]=[A,C[1]?Number(C[1]):1];s.push(C[0])})}y.lR=o(y.l||hljs.IR,true);if(typeof y.k=="string"){z("keyword",y.k)}else{for(var x in y.k){if(!y.k.hasOwnProperty(x)){continue}z(x,y.k[x])}}y.k=r}if(w){if(y.bWK){y.b="\\b("+s.join("|")+")\\s"}y.bR=o(y.b?y.b:"\\B|\\b");if(!y.e&&!y.eW){y.e="\\B|\\b"}if(y.e){y.eR=o(y.e)}y.tE=y.e||"";if(y.eW&&w.tE){y.tE+=(y.e?"|":"")+w.tE}}if(y.i){y.iR=o(y.i)}if(y.r===undefined){y.r=1}if(!y.c){y.c=[]}for(var v=0;v<y.c.length;v++){if(y.c[v]=="self"){y.c[v]=y}p(y.c[v],y)}if(y.starts){p(y.starts,w)}var u=[];for(var v=0;v<y.c.length;v++){u.push(y.c[v].b)}if(y.tE){u.push(y.tE)}if(y.i){u.push(y.i)}y.t=u.length?o(u.join("|"),true):{exec:function(t){return null}}}p(q)}function d(D,E){function o(r,M){for(var L=0;L<M.c.length;L++){var K=M.c[L].bR.exec(r);if(K&&K.index==0){return M.c[L]}}}function s(K,r){if(K.e&&K.eR.test(r)){return K}if(K.eW){return s(K.parent,r)}}function t(r,K){return K.i&&K.iR.test(r)}function y(L,r){var K=F.cI?r[0].toLowerCase():r[0];return L.k.hasOwnProperty(K)&&L.k[K]}function G(){var K=l(w);if(!A.k){return K}var r="";var N=0;A.lR.lastIndex=0;var L=A.lR.exec(K);while(L){r+=K.substr(N,L.index-N);var M=y(A,L);if(M){v+=M[1];r+='<span class="'+M[0]+'">'+L[0]+"</span>"}else{r+=L[0]}N=A.lR.lastIndex;L=A.lR.exec(K)}return r+K.substr(N)}function z(){if(A.sL&&!e[A.sL]){return l(w)}var r=A.sL?d(A.sL,w):g(w);if(A.r>0){v+=r.keyword_count;B+=r.r}return'<span class="'+r.language+'">'+r.value+"</span>"}function J(){return A.sL!==undefined?z():G()}function I(L,r){var K=L.cN?'<span class="'+L.cN+'">':"";if(L.rB){x+=K;w=""}else{if(L.eB){x+=l(r)+K;w=""}else{x+=K;w=r}}A=Object.create(L,{parent:{value:A}});B+=L.r}function C(K,r){w+=K;if(r===undefined){x+=J();return 0}var L=o(r,A);if(L){x+=J();I(L,r);return L.rB?0:r.length}var M=s(A,r);if(M){if(!(M.rE||M.eE)){w+=r}x+=J();do{if(A.cN){x+="</span>"}A=A.parent}while(A!=M.parent);if(M.eE){x+=l(r)}w="";if(M.starts){I(M.starts,"")}return M.rE?0:r.length}if(t(r,A)){throw"Illegal"}w+=r;return r.length||1}var F=e[D];f(F);var A=F;var w="";var B=0;var v=0;var x="";try{var u,q,p=0;while(true){A.t.lastIndex=p;u=A.t.exec(E);if(!u){break}q=C(E.substr(p,u.index-p),u[0]);p=u.index+q}C(E.substr(p));return{r:B,keyword_count:v,value:x,language:D}}catch(H){if(H=="Illegal"){return{r:0,keyword_count:0,value:l(E)}}else{throw H}}}function g(s){var o={keyword_count:0,r:0,value:l(s)};var q=o;for(var p in e){if(!e.hasOwnProperty(p)){continue}var r=d(p,s);r.language=p;if(r.keyword_count+r.r>q.keyword_count+q.r){q=r}if(r.keyword_count+r.r>o.keyword_count+o.r){q=o;o=r}}if(q.language){o.second_best=q}return o}function i(q,p,o){if(p){q=q.replace(/^((<[^>]+>|\t)+)/gm,function(r,v,u,t){return v.replace(/\t/g,p)})}if(o){q=q.replace(/\n/g,"<br>")}return q}function m(r,u,p){var v=h(r,p);var t=a(r);if(t=="no-highlight"){return}var w=t?d(t,v):g(v);t=w.language;var o=c(r);if(o.length){var q=document.createElement("pre");q.innerHTML=w.value;w.value=j(o,c(q),v)}w.value=i(w.value,u,p);var s=r.className;if(!s.match("(\\s|^)(language-)?"+t+"(\\s|$)")){s=s?(s+" "+t):t}r.innerHTML=w.value;r.className=s;r.result={language:t,kw:w.keyword_count,re:w.r};if(w.second_best){r.second_best={language:w.second_best.language,kw:w.second_best.keyword_count,re:w.second_best.r}}}function n(){if(n.called){return}n.called=true;Array.prototype.map.call(document.getElementsByTagName("pre"),b).filter(Boolean).forEach(function(o){m(o,hljs.tabReplace)})}function k(){window.addEventListener("DOMContentLoaded",n,false);window.addEventListener("load",n,false)}var e={};this.LANGUAGES=e;this.highlight=d;this.highlightAuto=g;this.fixMarkup=i;this.highlightBlock=m;this.initHighlighting=n;this.initHighlightingOnLoad=k;this.IR="[a-zA-Z][a-zA-Z0-9_]*";this.UIR="[a-zA-Z_][a-zA-Z0-9_]*";this.NR="\\b\\d+(\\.\\d+)?";this.CNR="(\\b0[xX][a-fA-F0-9]+|(\\b\\d+(\\.\\d*)?|\\.\\d+)([eE][-+]?\\d+)?)";this.BNR="\\b(0b[01]+)";this.RSR="!|!=|!==|%|%=|&|&&|&=|\\*|\\*=|\\+|\\+=|,|\\.|-|-=|/|/=|:|;|<|<<|<<=|<=|=|==|===|>|>=|>>|>>=|>>>|>>>=|\\?|\\[|\\{|\\(|\\^|\\^=|\\||\\|=|\\|\\||~";this.BE={b:"\\\\[\\s\\S]",r:0};this.ASM={cN:"string",b:"'",e:"'",i:"\\n",c:[this.BE],r:0};this.QSM={cN:"string",b:'"',e:'"',i:"\\n",c:[this.BE],r:0};this.CLCM={cN:"comment",b:"//",e:"$"};this.CBLCLM={cN:"comment",b:"/\\*",e:"\\*/"};this.HCM={cN:"comment",b:"#",e:"$"};this.NM={cN:"number",b:this.NR,r:0};this.CNM={cN:"number",b:this.CNR,r:0};this.BNM={cN:"number",b:this.BNR,r:0};this.inherit=function(q,r){var o={};for(var p in q){o[p]=q[p]}if(r){for(var p in r){o[p]=r[p]}}return o}}();hljs.LANGUAGES.php=function(a){var e={cN:"variable",b:"\\$+[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*"};var b=[a.inherit(a.ASM,{i:null}),a.inherit(a.QSM,{i:null}),{cN:"string",b:'b"',e:'"',c:[a.BE]},{cN:"string",b:"b'",e:"'",c:[a.BE]}];var c=[a.BNM,a.CNM];var d={cN:"title",b:a.UIR};return{cI:true,k:"and include_once list abstract global private echo interface as static endswitch array null if endwhile or const for endforeach self var while isset public protected exit foreach throw elseif include __FILE__ empty require_once do xor return implements parent clone use __CLASS__ __LINE__ else break print eval new catch __METHOD__ case exception php_user_filter default die require __FUNCTION__ enddeclare final try this switch continue endfor endif declare unset true false namespace trait goto instanceof insteadof __DIR__ __NAMESPACE__ __halt_compiler",c:[a.CLCM,a.HCM,{cN:"comment",b:"/\\*",e:"\\*/",c:[{cN:"phpdoc",b:"\\s@[A-Za-z]+"}]},{cN:"comment",eB:true,b:"__halt_compiler.+?;",eW:true},{cN:"string",b:"<<<['\"]?\\w+['\"]?$",e:"^\\w+;",c:[a.BE]},{cN:"preprocessor",b:"<\\?php",r:10},{cN:"preprocessor",b:"\\?>"},e,{cN:"function",bWK:true,e:"{",k:"function",i:"\\$|\\[|%",c:[d,{cN:"params",b:"\\(",e:"\\)",c:["self",e,a.CBLCLM].concat(b).concat(c)}]},{cN:"class",bWK:true,e:"{",k:"class",i:"[:\\(\\$]",c:[{bWK:true,eW:true,k:"extends",c:[d]},d]},{b:"=>"}].concat(b).concat(c)}}(hljs);
    </script>

    <script type="text/javascript">
window.onload = function() {
    var codeBlocks = document.getElementsByTagName('pre'),
        callStackItems = document.getElementsByClassName('call-stack-item');

    // highlight code blocks
    for (var i = 0, imax = codeBlocks.length; i < imax; ++i) {
        hljs.highlightBlock(codeBlocks[i], '    ');
    }

    var refreshCallStackItemCode = function(callStackItem) {
        if (!callStackItem.getElementsByTagName('pre')[0]) {
            return;
        }
        var top = callStackItem.getElementsByClassName('code-wrap')[0].offsetTop - window.pageYOffset + 3,
            lines = callStackItem.getElementsByTagName('pre')[0].getClientRects(),
            lineNumbers = callStackItem.getElementsByClassName('lines-item'),
            errorLine = callStackItem.getElementsByClassName('error-line')[0],
            hoverLines = callStackItem.getElementsByClassName('hover-line');
        for (var i = 0, imax = lines.length; i < imax; ++i) {
            if (!lineNumbers[i]) {
                continue;
            }
            lineNumbers[i].style.top = parseInt(lines[i].top - top) + 'px';
            hoverLines[i].style.top = parseInt(lines[i].top - top) + 'px';
            hoverLines[i].style.height = parseInt(lines[i].bottom - lines[i].top + 6) + 'px';
            if (parseInt(callStackItem.getAttribute('data-line')) == i) {
                errorLine.style.top = parseInt(lines[i].top - top) + 'px';
                errorLine.style.height = parseInt(lines[i].bottom - lines[i].top + 6) + 'px';
            }
        }
    };

    for (var i = 0, imax = callStackItems.length; i < imax; ++i) {
        refreshCallStackItemCode(callStackItems[i]);

        // toggle code block visibility
        callStackItems[i].getElementsByClassName('element-wrap')[0].addEventListener('click', function() {
            var callStackItem = this.parentNode,
                code = callStackItem.getElementsByClassName('code-wrap')[0]
            code.style.display = window.getComputedStyle(code).display == 'block' ? 'none' : 'block';
            refreshCallStackItemCode(callStackItem);
        });
    }
};

    // Highlight lines that have text in them but still support text selection:
    document.onmousedown = function() { document.getElementsByTagName('body')[0].classList.add('mousedown'); }
    document.onmouseup = function() { document.getElementsByTagName('body')[0].classList.remove('mousedown'); }
    </script>
    <?php if (method_exists($this, 'endBody')) $this->endBody(); // to allow injecting code into body (mostly by KantPHP Debug Toolbar) ?>
</body>

</html>
<?php if (method_exists($this, 'endPage')) $this->endPage(); ?>
