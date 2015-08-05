<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
        <meta name="description" content="KantPHP帮助文档">
        <meta name="author" content="KantPHP帮助文档">
        <link rel="icon" href="../../favicon.ico">

        <title>KantPHP帮助文档</title>

        <!-- Bootstrap core CSS -->
        <link href="<?php echo PUBLIC_URL; ?>help/css/bootstrap.min.css" rel="stylesheet">

        <!-- Custom styles for this template -->
        <link rel="stylesheet" href="<?php echo PUBLIC_URL; ?>help/css/style.css">
        <!-- Bootstrap core JavaScript
        ================================================== -->
        <!-- Placed at the end of the document so the pages load faster -->
        <script src="<?php echo PUBLIC_URL; ?>help/js/jquery.min.js"></script>
        <script src="<?php echo PUBLIC_URL; ?>help/js/bootstrap.min.js"></script>

        <!-- Just for debugging purposes. Don't actually copy these 2 lines! -->
        <!--[if lt IE 9]><script src="../../assets/js/ie8-responsive-file-warning.js"></script><![endif]-->
        <script src="<?php echo PUBLIC_URL; ?>help/js/ie-emulation-modes-warning.js"></script>

        <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
        <!--[if lt IE 9]>
          <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
          <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
        <![endif]-->
    </head>
    <body data-spy="scroll" data-target="#myScrollspy">
        <div class="help-masthead">
            <div class="container">
                <nav class="help-nav">
                    <a class="help-nav-item " href="http://www.kantphp.com">KantPHP</a>
                    <a class="help-nav-item active" href="#">帮助文档</a>
                </nav>
            </div>
        </div>
        <div class="container">
            <div class="help-header">
                <h1 class="help-title">KantPHP帮助文档</h1>
            </div>
            <div class="row">
                <div class="col-xs-3" id="myScrollspy">
                    <ul class="nav nav-tabs nav-stacked" data-spy="affix" data-offset-top="125">
                        <li class="active"><a href="#welcome">1.欢迎使用KantPHP</a></li>
                        <li><a href="#faststart">2.快速开始</a></li>
                        <li><a href="#schema">3.架构原理</a></li>
                        <li><a href="#configure">4.项目配置</a></li>
                        <li><a href="#module">5.模块化开发</a></li>
                        <li><a href="#mvcpattern">6.MVC模式</a></li>
                        <li><a href="#controller">7.控制器</a></li>
                        <li><a href="#view">8.视图</a></li>
                        <li><a href="#model">9.模型</a></li>
                        <li><a href="#cookie">10.Cookie</a></li>
                        <li><a href="#session">11.Session</a></li>
                        <li><a href="#cache">12.Cache</a></li>
                        <li><a href="#rewrite">13.路由与重写</a></li>
                        <li><a href="#extend">14.扩展</a></li>
                        <li><a href="#thirdparty">15.第三方类库</a></li>
                    </ul>
                </div>
                <div class="col-xs-9 col-sm-8 help-main">
                    <div class="help-post" id="welcome">
                        <div class="page-header">
                            <h2 class="help-post-title">1. 欢迎使用KantPHP</h2>
                        </div>
                        <p>感谢您使用KantPHP Framework!如果您遇到其他在此文档中没有提及的问题，请有发送邮件到【洞主】邮箱zhenqiang.zhang@hotmail.com，会收到尽快回复。</p>
                        <hr>
                        <p>KantPHP Framework是一个快速、基于PHP5.3+的PHP开发框架。作为开源软件，可以自由下载和使用，遵循 BSD 3-Clause license 。</p>
                        <p><a href="https://github.com/bearlord/kantphp/releases" target="_blank">前往Github下载</a></p>
                        <blockquote>
                            <p>Kantphp Framework起初是洞主为学习PHP框架而写的一个实验室产品，至2015年有3年的开发周期，共发布了8个版本，虽然仅仅是默默无闻。KantPHP Framework 有很多国内外出名的PHP框架的影子,如：<b>Zend Framework</b>, <b>CodeIgniter Web Framework</b>, <b>CakePHP Framework</b>, <b>ThinkPHP</b> 和其他的开源系统如：<b>PHPCMS</b>, <b>Discuz!</b> 借鉴前辈和同行的很多思想和思路，同时也引用了在工作和项目中收集的一些开源类库。</p>
                            <p>洞主是一个除了研究《西游记》积极，其他方面都比较懒且有拖延症的程序员，感谢Unix/Linux运维群里诸多群友的肯定和热情的鼓励并敦促我写KantPHP帮助文档和示例代码。</p>
                        </blockquote>
                        <p>KantPHP Framework采用模块化的MVC开发模式，简洁易懂。开发者如果学过其他框架，会发现很容易学会KantPHP Framework。对于初学者，也很容易入门。</p>
                        <p>版本1.2中暂未引入命名空间【namespace】，在未来版本中会加入此功能。</p>
                    </div><!-- /.help-post -->

                    <div class="help-post" id="faststart">
                        <div class="page-header">
                            <h2 class="help-post-title">2. 快速开始</h2>
                        </div>
                        <h3>2.1 环境要求</h3>
                        <ul>
                            <li>Web服器：Apache,Nginx,LightHttpd</li>
                            <li>PHP5.3+</li>
                            <li>数据库：PostgreSQL,Mysql,Sqilte</li>
                        </ul>
                        <blockquote>
                            <p>Apache和Nginx要开启Rewrite，Nginx要设置支持Pathinfo。</p>
                            <p>本文档的示例代码以Aapache作为WEB服务器</p>
                            <p>PHP打开常用扩展如：php_gd,php_mbstring,php_curl以及连接数据库的扩展，如果连接PostgreSQL,建议采用PDO扩展连接。</p>
                        </blockquote>
                        <h3>2.2 获取KantPHP Framework</h3>
                        <p>从<a href="https://github.com/bearlord/kantphp/releases" target="_blank">Github</a>获取KantPHP的发行版或者<a href="https://github.com/bearlord/kantphp/">Git Clone</a>最新版，解压并复制到WEB服务器根目录，如/srv/www/htdocs/kantphp，并赋予目录kantphp写权限和可执行权限。如果是开发环境，粗暴的设置为 <em>0777</em> 是不错的选择。我们会在后续署章节中再讨论权限。</p>
                        <p>打开浏览器，输入<em>http://www.kantphp.com/kantphp/</em>，如果你见到页面上显示：</p>
                        <blockquote>
                            <p>Welcome to KantPHP Framework</p>
                        </blockquote>
                        <p>说明开发环境配置成功。</p>
                        <p>你可以继续浏览 </p>
                        <blockquote>
                            <p><em>http://www.kantphp.com/kantphp/demo/index/display</em></p>
                            <p><em>http://www.kantphp.com/kantphp/demo/index/displayfunc</em></p>
                            <p><em>http://www.kantphp.com/kantphp/demo/index/get/var,abc</em></p>
                            <p><em>http://www.kantphp.com/kantphp/demo/index/get/var,abc.html</em></p>
                        </blockquote>
                        <p><em>http://www.kantphp.com/kantphp</em>是网站的根目录，<em>demo/index/get/var,foo.html</em>则是参数，demo是Moduel Name,index是Controller Name,get是Action Name。var,abc等同&var=abc，是parse_url的query部分。html表示一个网页的后缀，可有可无。</p>
                        <p>一个完整的URL访问规则是：</p>
                        <blockquote>
                            <p>http://www.kantphp.com/kantphp/[模型名称]/[控制器名称]/[操作名称]/[参数名,参数值]/...[.html]</p>
                        </blockquote>
                    </div><!-- /.help-post -->
                    <div class="help-post" id="schema">
                        <div class="page-header">
                            <h2 class="help-post-title">3. 架构原理</h2>
                        </div>
                        <h3>3.1 入口</h3>
                        <p>程序的入口文件是index.php。</p>
                        <h3>3.2 流程图</h3>
                        <p>流程图完善中。</p>
                    </div><!-- /.help-post -->
                    <div class="help-post" id="configure">
                        <div class="page-header">
                            <h2 class="help-post-title">4. 项目配置</h2>
                        </div>
                        <h3>4.1 路径</h3>
                        <p>配置文件位于/Applcation/Config/。</p>
                        <h3>4.2 环境</h3>
                        <p>为方便项目开发与生产配置，可在入口文件选择环境。以最小改动的代价，有利于迭代开发与版本更替。</p>
                        <blockquote>
                            <p><em>Kant::createApplication('Development')->boot();</em> 表示开发环境，加载的配置文件为：<em>/Applcation/Config/Deveplopment/Config.php</em></p>
                            <p><em>Kant::createApplication('Production')->boot(); </em> 表示生产环境，加载的配置文件为：<em>/Applcation/Config/Deveplopment/Config.php</em></p>
                        </blockquote>
                        <h3>4.3 说明</h3>
                        <blockquote>
                            <p>配置文件以key=>value的数组形式保存</p>
                            <p><code> 'module' => 'demo'</code> 默认的模块Moduel</p>
                            <p><code> 'ctrl' => 'index',</code> 默认的控制器Controller</p>
                            <p><code> 'act' => 'index',</code> 默认的动作Action</p>
                            <p><code> 'data' => array('GET' => array()),</code> 默认的参数Parameter</p>
                            <p><code> 'route_rules' => array(
                                    '|topic/id,(\d+)|i' => 'blog/detail/index/id,$1/c,$2'
                                    ),</code> Rewrite规则，可用正则表达式</p>
                            <p><code> 'path_info_repair' => false,</code> 是否开启Pathinfo修复。如果你的Web服务器不支持Pathinfo，开启此设置。</p>
                            <p><code> 'debug' => true,</code> 是否开启调试模式</p>
                            <p><code> 'url_suffix' => '.html',</code> URL后缀</p>
                            <p><code> 'redirect_tpl' => 'dispatch/redirect',</code> 页面跳转模板</p>
                            <p><code> 'lang' => 'zh_CN',</code> 默认语言。对应的语言包文件是<em>/Application/Locale/zh_CN/App.php。</em></p>
                            <p><code> 'charset' => 'utf-8',</code> 默认编码</p>
                            <p><code> 'default_timezone' => 'Etc/GMT-8',</code> 默认时区</p>
                            <p><code> 'database' => array('deault'=>array()...),</code> 数据库配置。可配置多个数据库，通过模型Model来操作。</p>
                            <p><code> 'cookie_domain' => '',</code> Cookie作用域</p>
                            <p><code> 'cookie_path' => '/',</code> Cookie路径</p>
                            <p><code> 'cookie_pre' => 'kantphp_',</code> Cookie前缀</p>
                            <p><code> 'cookie_ttl' => 0,</code> Cookie失效时间</p>
                            <p><code> 'session' => array(
                                    'default' =>array()...),</code> Session配置。Session可以默认保存，也可以保存到指定路径，数据库等。</p>
                            <p><code> 'cache' => array(
                                    'defalut'=>array()...),</code> 缓存配置。选项有文件缓存，Memcache，Redis缓存。</p>
                            <p>如果你希望自定义配置，继续写入key=>value键值对。<code></code></p>
                        </blockquote>
                        <h3>4.4 读取配置</h3>
                        <p>为提高执行效率，项目初始化时，配置文件以注册模式载入了内存，不需要再读取文件。而且在开发和生产环境之间的来回切换，直接读取文件有不可预知的问题。</p>
                        <blockquote>
                            <p><code>$config = KantRegistry::get('config');$lang = $config['lang']; </code>读取项目配置的默认语言。</code></p>
                        </blockquote>
                    </div><!-- /.help-post -->
                    <div class="help-post" id="module">
                        <div class="page-header">
                            <h2 class="help-post-title">5. 模块化开发</h2>
                        </div>
                        <p>通俗一点讲，模块就是把源文件进行分割。模块化由小块的、分散的代码块组成，每一块都是独立的。这些代码块可以由不同的团队进行开发，而他们都有各自的生命周期和时间表。最终将模块进行集成。</p>
                        <h3>5.1 优点</h3>
                        <ol>
                            <li>思路清晰。水平分割项目后，大项目变小项目，容易理清思路，避免遗漏和陷阱。</li>
                            <li>减少开发时间。分布式开发，减少与其他模块开发者的交流与等待时间。</li>
                            <li>维护灵活。一个模块出现问题，不会影响到其他模块。单独调试此模块解决问题。</li>
                            <li>管理方便。升级时粗暴覆盖全目录，不用花很长时间整理更新文件。</li>
                        </ol>
                        <h3>5.2 创建</h3>
                        <p>KantPHP Framework 没有通过Shell或者PHP Cli创建模块的自动化工具。</p>
                        <p><em>/Application/Module/</em>是模块根目录。<em>/Application/Module/Demo</em> 就代表Demo模块。</p>
                        <p>如果要创建新的模块，手动创建一个文件夹即可。<small>【文绉绉的说了很多，其实操作太简单。】</small></p>
                        <p>尽量避免跨模块调用代码。</p>
                        <p><b class="color-emphasize">为了下文举例方便，假设现在我们创建了一个Blog的模块。</b></p>
                    </div><!-- /.help-post -->
                    <div class="help-post" id="mvcpattern">
                        <div class="page-header">
                            <h2>6. MVC模式</h2>
                        </div>
                        <p>MVC 是一种使用 MVC（Model View Controller 模型-视图-控制器）设计创建 Web 应用程序的模式:</p>
                        <ol>
                            <li>Model（模型）表示应用程序核心（比如数据库记录列表）。是应用程序中用于处理应用程序数据逻辑的部分。通常模型对象负责在数据库中存取数据。</li>
                            <li>View（视图）是应用程序中处理数据显示的部分。通常视图是依据模型数据创建的。</li>
                            <li>Controller（控制器）是应用程序中处理用户交互的部分。通常控制器负责从视图读取数据，控制用户输入，并向模型发送数据。</li>
                        </ol>
                        <p>MVC 分层同时也简化了分组开发。不同的开发人员可同时开发视图、控制器逻辑和业务逻辑。</p>
                        <p>KantPHP Framework在设计之初就考虑了多库操作，同时考虑到数据库连接数，执行效率等因素，采用了比较严格的MVC模式。控制器或者模板里不能直接数据库datebase，只能通过模型来访问。</p>
                        <p>优点：维护升级方便，几乎所有的操作都封装在Model中。缺点：多敲代码封装函数，同时新手需要一个适应的过程。</p>
                    </div><!-- /.help-post -->
                    <div class="help-post" id="controller">
                        <div class="page-header">
                            <h2>7. 控制器</h2>
                        </div>
                        <p>控制器就是一个类，处理浏览器请求和响应，操作模型，赋值到视图，渲染视图等操作。</p>
                        <p>用户通过浏览器访问应用，URL发送的请求会通过入口文件生成一个应用实例，应用控制器会管理整个用户执行的过程，并负责模块的调度和动作的执行，并且在最后销毁该应用实例。任何一个URL访问都可以认为是某个模块的某个操作，例如：</p>
                        <blockquote>
                            <p>http://www.kantphp.com/kantphp/blog/list/category/id,8.html</p>
                            <p>http://www.kantphp.com/kantphp/blog/detail/index/id,100.html</p>
                        </blockquote>
                        <p>系统会根据当前的URL来分析要执行的模块和操作。这个分析工作由URL调度器（Dispatcher）来实现，并且都分析成下面的规范：</p>
                        <blockquote>
                            <p>http://域名/项目名/模块名/控制器名/动作名/其他参数/URL后缀</p>
                            <p>Dispatcher会根据URL地址来获取当前需要执行的项目名、模块名，控制器名，动作名以及其他参数，在某些情况下，项目名可能不会出现在URL地址中。</p>
                            <p>控制器类名就是控制器名加上Controller后缀，例如ListContoller类就表示了List控制器。而category动作其实就是ListController类的一个公共方法。</p>
                            <p>所以我们在浏览器里面输入URL：<em>http://www.kantphp.com/kantphp/blog/list/category/id,8.html</em>其实就是执行了ListControoler类的【category加Action后缀】（公共）方法。</p>
                        </blockquote>
                        <h3>7.1 定义</h3>
                        <p>控制器定义规则为【控制器名+Contrller后缀】，如IndexController。同时要继承BaseController。
                        <p>在<a href="#module">【5. 模块化开发】</a>中我们已经创建了Blog模块，文件路径是<em>/Application/Moduel/Blog/</em>。现在我们要创建一个IndexController的。进入Blog目录下创建一个Controller文件夹，进入Controoler文件夹，创建文件<em>IndexController.php</em>。内容如下：</p>                      
                        <blockquote>
                            <p>class ListController extends BaseController{}</p>
                            <p></p>
                        </blockquote></p>
                        <h3>7.2 空控制器</h3>
                        <p>如果当前系统找不到指定的控制器，会尝试定位空控制器。我们可以定义错误页面和用户体验的优化。文件名为EmptyController.php。内容如下：</p>
                        <blockquote>
                            <p>class EmptyController extends BaseController{}</p>
                        </blockquote>
                        <h3>7.3 动作</h3>
                        <p>控制器可以有多个动作Action，正如一个类可以有多个方法。方法名的定义为【小写的动作名称加Action后缀】，如：</p>
                        <blockquote>
                            <ol class="linenums">
                                <li><code>class ListController extends BaseController{</code><br /></li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;</span><code>public function categoryAction(){}</code></li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;</span><code>public function orderbydescAction(){}</code></li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;</span><code>...</code></li>
                                <li><code>}</code></li>
                            </ol>
                        </blockquote>
                        <h3>7.4 常用方法</h3>
                        <p>控制器继承父类BaseContorller类与Base类，父类的公共方法，动作中均可调用。</p>
                        <blockquote>
                            <ol>
                                <li>处理URL请求的数据。$this->input对象包含的方法。如：
                                    <ol class="linenums">
                                        <li><code>$id = $this->input->get('id', 'intval', 10);</code> 等同于<br /><code>$id = !empety($_GET['id']) ? intval($_GET['id']) : 10</code> 。</li>
                                        <li><code>$username = $this->input->post('username', 'trim');</code> 等同于<br /><code>if(!empty($_POST['username'])) {</code><br /><code><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;</span>$username = trim($_POST['username']); </code><br /><code>} </code>。</li>
                                    </ol>
                                <li>缓存数据。$this->cache对象包含的方法。如：
                                    <ol class="linenums">
                                        <li><code>$this->cache->set('var', 'hello world); </code> 缓存字符串'hello world'，查找的键为'var'。</li>
                                        <li><code>$this->cache->get('var');</code> 查找键为'var'的缓存内容。</li>
                                    </ol>
                                <li>多语言输出。$this->lang方法。如
                                    <ol class="linenums">
                                        <li><code>echo $this->lang('USERNAME_IS_EMPTY');</code> 输出项目当前语言【如zh_CN】翻译过的'USERNAME_IS_EMPTY'。翻译的文件位于：<em>/Application/Locale/zh_CN/App.php</em>。如之前已追加过 <code>$LANG['USERNAME_IS_EMPTY'] = '用户名为空！'</code>则会输出【用户名为空！】。如果没有此键值对，则会原样输出【USERNAME_IS_EMPTY】。</li>
                                        <li>如果想增加其他语言，如英语。新建文件<em>/Application/Locale/en_US/App.php</em>，并在配置文件中更改<code>'lang' => 'en_US'</code>即可</li>
                                    </ol>
                                </li>
                                <li>加载模型。$this->model()方法。如：
                                    <ol class="linenums">
                                        <li><code>$memberModel = $this->model('Member');</code> 等同于<br /><code>require_once [当前模块]/Model/MemberModel.php;</code><br /><code>$memberModel = new MemberModel();</code></li>
                                        <li><b>不推荐跨模块加载模型。</b></li>
                                    </ol>
                                </li>
                                <li>生成URL连接。$this->url()方法。如：</li>
                            </ol>
                        </blockquote>
                        <h3>7.5 赋值到视图</h3>
                        <p>要在视图中输出变量，必须在控制器类中把变量传递给视图。用公共属性赋值即可。如：</p>
                        <blockquote>
                            <p><code>$this->view->helloString = 'Hello world';</code></p>
                            <p><code>$this->view->listArray = array(1,3,5,7,9)</code></p>
                            <p><code>$this->view->infoArray = array('name' => 'KantPHP', 'address' => '太行山')</code></p>
                        </blockquote>
                        <p><b>不推荐传递实例化对象。可在视图中实例化。</b></p>
                    </div>
                    <div class="help-post" id="view">
                        <div class="help-header">
                            <h2>8. 视图</h2>
                            <p>视图就是模板文件，就是一个网页。控制器把要输出的数据通过模板变量赋值的方式传递到视图类，视图输出内容到浏览器。</p>
                            <p>一般来说，视图都带有模板引擎。模板引擎把模板的伪代码解析成PHP原生态代码，才可正常运行。为避免重复解析，原生态代码一般写入缓存成文件，触发更新。当目标文件无法写入或者需要间接的写入Memcache/Redis内存曲线保存时，一则影响到效率，二则不适合环境迁移。三是恼人的回调函数问题可轻松通过原生态 PHP 函数解决。</p>
                            <p>KantPHP Framework不带模板引擎，初衷是为了适应新浪SAE，老版本的百度BAE等代码目录没有写入权限的空间和运行环境。开发者可以修改<em>/Application/Kantphp/View/View.php</em>，自行加载模板引擎。</p>
                            <h3>8.1 定义</h3>
                            <p>视图可选择主题，默认的主题是【default】。视图的根目录是/Application/View/default/。如果开发者想更换主题，比如换为【blue】，修改配置文件 <em>'theme' => 'blue'</em>，此时视图的根目录则变为<em>/Application/View/blue</em>。</p>
                            <p>视图定义规则为：</p>
                            <blockquote>
                                <p>视图根目录/模型名/控制器名/动作名/+模板后缀。比如：</p>
                                <p><em>/Application/View/default/blog/comment/apply.php</em></p>
                            </blockquote>
                            <h3>8.2 视图赋值</h3>
                            <p>视图的数据，需要控制器类把变量传递给视图。通过视图类的公开属性赋值。例如：</p>
                            <blockquote>
                                <p>在控制器中：</p>
                                <p><code>$this->view->hello = 'Hello World';</code></p>
                                <p><code>$this->view->userInfo = array('name' => '欢乐的洞主', 'address' => '河南郑州');</code></p>
                                <p>在视图中：</p>
                                <p><code>echo $hello;</code> 解析后是【'Hello World'】</p>
                                <p><code>echo $userInfo['name'];  echo $userInfo['address'];</code> 解析后是【欢乐的洞主 河南郑州】</p>
                            </blockquote>
                            <h3>8.3 视图输出</h3>
                            <p>视图变量赋值后，需要调用模板文件来输出相关的变量，视图调用通过display方法来实现。我们在控制的动作方法的最后使用：</p>
                            <blockquote>
                                <p><code>$this->view->display();</code> 调用默认视图</p>
                                <p><code>$this->view->display('list/ajaxpage');</code> 调用指定视图</p>
                                <p><code>$this->view->display('list/ajaxpage', 'blog');</code> 调用指定模块的指定视图</p>
                            </blockquote>
                            <p>就可以输出模板。根据前面的视图定义规则，系统会按照默认规则自动定位视图文件，通常display方法无需带任何参数即可输出对应的视图。</p>
                            <h3>8.4 获取内容</h3>
                            <p>如果开发者不想直接输出模板内容，而是存入变量，可以使用fetch方法来获取视图内容</p>
                            <blockquote>
                                <p><code>$content = $this->view->fetch();</code></p>
                                <p>fetch的参数用法和display方法基本一致。</p>
                            </blockquote>
                            <h3>8.5 替代控制结构</h3>
                            <p>视图文件中使用原始 PHP 代码。要使 PHP 代码达到最精简并使其更容易辨认，因此建议你使用 PHP 替代语法控制结构。如</p>
                            <blockquote>
                                <p><code><?php echo htmlspecialchars("<?php"); ?> foreach ($todo as $item): <?php echo htmlspecialchars("?>"); ?></code></p>
                                <p><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;</span><code><?php echo htmlspecialchars("<?php"); ?> echo $item; <?php echo htmlspecialchars("?>"); ?></code></p>
                                <p><code><?php echo htmlspecialchars("<?php"); ?> endforeach; <?php echo htmlspecialchars("?>"); ?></code></p>
                            </blockquote>
                        </div>
                    </div>
                    <div class="help-post" id="model">
                        <div class="page-header">
                            <h2>9 模型</h2>
                        </div>
                        <p>是的，模型也是一个类，是用于处理应用程序数据逻辑的部分，通常负责数据库的存取操作。KantPHP Framework在基础 Model 类中完成了基本的CURD、ActiveRecord模式、连贯操作和统计查询。</p>
                        <h3>9.1 定义</h3>
                        <p>模型定义规则为【模型名+Model后缀】，如CategoryModel。同时要继承BaseModel。</p>
                        <p>回顾之前的操作，在<a href="#module">【5. 模块化开发】</a>中我们已经创建了Blog模块，在<a href="#controller">【7. 控制器】</a>中我们创建了一个控制器。文件路径是<em>/Application/Moduel/Blog/Controller/IndexControoler.php</em>。</p>
                        <p>现在我们创建一个CategoyModel。文件路径是：<em>/Application/Moduel/Blog/Model/CategoryModel.php</em>。内容如下：</p>
                        <blockquote>
                            <ol class="linenums">
                                <li><code>class Cateogyr extends BaseModel{</code></li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;</span><code>protected $table = 'category;</code> 每个模型应对应一个主表，表名称排除表前缀部分。</li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;</span><code>protected $primary = 'id';</code> 每个主表应有一个主键</li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;</span><code>...</code></li>
                                <li><code>}</code></li>
                            </ol>
                        </blockquote>
                        <p><b>只有在模型里指定了$table，才能在实例化模型后，操作数据表。</b></p>
                        <h3>9.1 模型实例化</h3>
                        <p>模型实例化也就是实例化一个类，原理无非是include class; new class。KantPHP Framework封装了 model 方法简化了这两个步骤。如：</p>
                        <blockquote>
                            <p><code>$CategoryMdoel = $this->model('Category')</code> 等同于：</p>
                            <p><code>include "/Application/Moduel/Blog/Model/CategoryModel.php"; </code></p>
                            <p><code>$CategoryModel = new CategoryModel();</code></p>
                            <p><b>注意：KantPHP Framework底层中有根据类别名映射进行的自动加载，但加载应用模型需要人工加载。直接<code>$CategoryModel = new CategoryModel();</code>会报错找不到指定文件。</b></p>
                            <p>为执行效率着想，<code>$CategoryMdoel = $this->model('Category');</code> 已经把实例化的CategoryModel缓存静态变量。在单次的进程【粗略的理解为本次浏览器请求】中，再次调用$this->model('Category')会直接读取静态变量，不用再实例化模型。</p>
                            <p>model是基类方法，可以在控制器和模型中使用。</p>
                        </blockquote>
                        <h3>9.2 连接数据库</h3>
                        <p>KantPHP Framework内置了抽象数据库访问层，把不同的数据库操作封装起来，开发者只需要使用公共的Db类进行操作，而无需针对不同的数据库写不同的代码和底层实现，Db类会自动调用相应的数据库驱动来处理。目前的数据库包括PostgreSQL,MySQL、SqLite，包含了对PDO的支持。使用数据库，必须配置数据库连接信息。</p>
                        <p>虽然数据库信息写在模型里也可以成功运行，但为项目部署方便，也为协同开发着想，强烈建议都写在配置文件。模型里指定不同的适配器，即可成功连接数据库。下面举例说明连接两个数据库的例子。</p>
                        <blockquote>
                            <ol class="linenums">
                                <li><code>'database' => array(</code></li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;</span><code>'default' => array(</code> 默认适配器</li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><code>'hostname' => '192.168.1.111',</code> 主机名</li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><code>'port' => '5432',</code> 端口号</li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><code>'database' => 'kant_bbs',</code> 数据库名</li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><code>'username' => 'root',</code> 用户名</li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><code>'password' => 'root',</code> 密码</li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><code>'tablepre' => 'kant_',</code> 表前缀</li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><code>'charset' => 'UTF-8',</code> 字符集</li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><code> 'type' => 'pdo_pgsql',</code> 数据库驱动类型</li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><code>'debug' => true,</code> 是否开启调试</li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><code>'persistent' => 0,</code> 是否长连接</li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><code>'autoconnect' => 1</code> 是否自动连接,为1是才连接数据库</li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;</span><code>}，</code></li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;</span><code>'mysql_adapter' => array(</code> MySql适配器</li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><code> 'hostname' => '192.168.1.112',</code> 主机名</li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><code>'port' => '3306',</code> 端口号</li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><code>'database' => 'kant_member',</code> 数据库名</li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><code>'username' => 'root',</code> 用户名</li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><code>'password' => 'root',</code> 密码</li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><code>'tablepre' => 'kant_',</code> 表前缀</li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><code>'charset' => 'UTF-8',</code> 字符集</li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><code> 'type' => 'mysql',</code> 数据库驱动类型</li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><code>'debug' => true,</code> 是否开启调试</li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><code>'persistent' => 0,</code> 是否长连接</li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><code>'autoconnect' => 1</code> 是否自动连接,为1是才连接数据库</li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;</span><code>}，</code></li>
                                <li><code>}，</code></li>
                            </ol>
                        </blockquote>
                        <p>配置文件中有关数据库设置如上所示。如果希望在CategoryModel中连接【默认适配器】， 而在MemberModel中连接【MySql适配器】,只需要在指定<code>protected $adapter = 'default'; </code>或者<code>protected $adapter = 'mysql_adapter';</code>即可。因默认适配器已设为【default】，所以不指定适配器即表示用默认适配器连接。【又一大段文绉绉的话。通俗点讲，配置文件database写两个数据库配置，模型里指定 $adapter 调用哪一个。】</p>
                        <h3>9.3 创建数据</h3>
                        <p>控制器接受表单请求，并把数据存入数据库。这一过程一般在控制器的动作方法中,调用模型的 save 方法实现。如：</p>
                        <blockquote>
                            <p><code>$data = array('category_title' => 'Unix/Linux运维', 'category_description' => '自动化运维 虚拟化技术 云计算 系统架构 Q群：49199179');</code> key值要与数据表字段名一致</p>
                            <p><code>$CategoryModel = $this->model('category');</code></p>
                            <p><code>$row = $CategoryModel->save($data);</code></p>
                            <p>解析后的SQL为：</p>
                            <p><code>INSERT INTO kant_category ('category_title', 'category_description') VALUES ('Unix/Linux运维',  '自动化运维 虚拟化技术 云计算 系统架构 Q群：49199179');</code></p>
                            <p>返回值$row为数据表主键最后插入的id或是最后一个序列。MySQL、SqLite为前者，PostgreSQl为后者。如果 $row 为真，说明保存数据成功。</p>
                        </blockquote>
                        <h3>9.4 读取数据</h3>
                        <p>读取数据用 read 和 readAll 方法， 例子如下：</p>
                        <blockquote>
                            <p>1. read 方法</p>
                            <p><code>$CategoryModel = $this->model('category');</code></p>
                            <p><code>$row = $CategoryModel->read("category_title, category_description", "", 103);</code> 或者</p>
                            <p><code>$row = $CategoryModel->read("category_title, category_description", "category_id", 103);</code> 或者</p>
                            <p><code>$row = $CategoryModel->read("category_title, category_description", array('category_id' => 103));</code></p>
                            <p>解析后的SQL为：</p>
                            <p><code>SELECT category_title, category_description FROM kant_category WHERE category_id = 103</code></p>
                            <p>返回值 $row 为结果集。如果为空则为false</p>
                            <p>2. readAll 方法</p>
                            <p><code>$CategoryModel = $this->model('category');</code></p>
                            <p><code>$row = $CategoryModel->readAll("category_title, category_description", "category_id ASC");</code></p>
                            <p>解析后的SQL为：</p>
                            <p><code>SELECT category_title, category_description FROM kant_category ORDER BY category_id ASC</code></p>
                            <p>返回值 $row 为结果集。如果为空则为false</p>
                        </blockquote>
                        <h3>9.5 更新数据</h3>
                        <p>更新数据与创建数据类似，加入查询条件即可。</p>
                        <blockquote>
                            <p><code>$data = array('category_title' => 'Unix/Linux运维', 'category_description' => '自动化运维 虚拟化技术 云计算 系统架构 Q群：49199179');</code> key值要与数据表字段名一致</p>
                            <p><code>$CategoryModel = $this->model('category');</code></p>
                            <p><code>$row = $CategoryModel->save($data, array('category_id' => 103));</code> 或者</p>
                            <p><code>$row = $CategoryModel->save($data, 103);</code> 第二个参数为模型的主键对应值。</p>
                            <p>解析后的SQL为：</p>
                            <p><code>UPDATE kant_category SET category_title = 'Unix/Linux运维', category_description = '自动化运维 虚拟化技术 云计算 系统架构 Q群：49199179' WHERE category_id = 103</code></p>
                            <p>返回值$row为 UPDATE SQL 的执行结果,而不是影响的行数。true为成功，false为失败。</p>
                        </blockquote>
                        <h3>9.6 删除数据</h3>
                        <p>删除数据的例子如下：</p>
                        <blockquote>
                            <p><code>$CategoryModel = $this->model('category');</code></p>
                            <p><code>$row = $CategoryModel->delete(array('category_id' => 103));</code> 或者</p>
                            <p><code>$row = $CategoryModel->delete(103);</code> 第二个参数为模型的主键对应值。</p>
                            <p>解析后的SQL为：</p>
                            <p><code>DELETE FROM kant_category SET WHERE category_id = 103</code></p>
                            <p>返回值$row为 DELETE SQL 的执行结果。true为成功，false为失败。</p>
                        </blockquote>
                        <h3>9.7 复杂的CURD操作</h3>
                        <p>项目开发中，不仅仅是简单的对数据表的创建、读取、修改、删除操作。模型封装的 read, save, delete方法已不能满足开发要求。我们需要在模型里新建方法，然后在控制器里调用此方法。下面举例说明。</p>
                        <h4>9.7.1 分页</h4>
                        <p>分页是很经典的例子。当初洞主刚毕业去参加面试，就被问过很多次。现在我们要写一个博客列表。</p>
                        <p>URL访问地址为：<em>/blog/list/index</em>。加上分页 page 参数后为 /blog/list/index/page,[1,2,3...].html</p>
                        <p>加上查询条件&key=val如[blog_author = 洞主] 后为 /blog/list/index/key,val/page,[1,2,3...].html</p>
                        <p>对应控制器为:<em>/Application/Module/Blog/Controller/ListController.php</em>。对应动作为indexAcion。</p>
                        <blockquote>
                            <ol class="linenums">
                                <li><code>public function indexAction(){</code></li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;</span><code>$perPage = 15;</code> //每页15篇日志</li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;</span><code>$page = $this->input->get('page','intval', 1);</code> //当前页</li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;</span><code>$BlogModel = $this->model('Blog');</code> //加载Blog模型并实例化</li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;</span><code>$data = $BlogModel->getPageList($page, $perPage);</code> //传递分页参数，返回列表结果集。</li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;</span><code>$count = $data[0];</code> //博客总数</li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;</span><code>$blogList = $data[1];</code> //博客列表</li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;</span><code>$this->library('Page');</code> //引入分页类，不实例化。</li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;</span><code>$pageObj = new Page($count, $perPage);</code> //实例化分页类</li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;</span><code>$pages = $pageObj->show();</code> //分页展示</li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;</span><code>$this->view->blogList = $blogList;</code> //传值到视图</li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;</span><code>$this->view->pages = $pages;</code> //传值到视图</li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;</span><code>$this->view->dispaly();</code> 视图显示</li>
                                <li><code>}</code></li>
                            </ol>
                        </blockquote>
                        <p>对应的模型为：<em>/Application/Module/Blog/Model/BlogModel.php</em>。方法名为 getPageList</p>
                        <blockquote>
                            <ol class="linenums">
                                <li><code>class BlogModel extens BaseModel{</code></li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;</span><code>protected $table = 'blog';</code> //表名</li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;</span><code>protected $primary = 'id';</code> //主键</li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;</span><code>public function getPageList($page, $perPage){</code></li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><code>$data[0] = $this->db->from($this->table)->count();</code> //博客总数</li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><code>$data[1] = $this->db->from($this->table)->select("blog_title, blog_description, blog_thumb, blog_author, blog_date, blog_hits, blog_tags")->where('blog_author', '洞主')->page($page, $perPage)->fetch();</code> //当前页博客列表结果集</li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><code>return $data;</code></li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;</span><code>}</code></li>
                                <li><code>}</code></li>
                            </ol>
                        </blockquote>
                        <p>对应的视图为：<em>/Application/View/default/blog/list/index.php</em>。按PHP原生态语法输出即可</p>
                        <blockquote>
                            <ol class="linenums">
                                <li><code><?php echo htmlspecialchars('<h1><?php foreach($blogList as $key=>$val): ?></h1>'); ?></code></li>
                                <li><code><?php echo htmlspecialchars('<div>'); ?></code></li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;</span><code><?php echo htmlspecialchars('<h1>标题：<?php echo $val["blog_title"]; ?></h1>'); ?></code></li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;</span><code><?php echo htmlspecialchars('<p>描述：<?php echo $val["blog_description"]; ?></p>'); ?></code></li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;</span><code><?php echo htmlspecialchars('<p>作者：<?php echo $val["blog_author"]; ?></p>'); ?></code></li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;</span><code><?php echo htmlspecialchars('<p>发布日期：<?php echo $val["blog_date"]; ?></p>'); ?></code></li>
                                <li><code><?php echo htmlspecialchars('</div>'); ?></code></li>
                                <li><code><?php echo htmlspecialchars('<div class="pages"><?php ecoh $pages; ?></div>'); ?></code></li>
                            </ol>
                        </blockquote>
                        <h3>9.7.2 事务</h3>
                        <p>事务就是一段sql语句的批处理，但是这个批处理是一个atom（原子），不可分割，要么都执行，要么回滚（rollback）都不执行。</p>
                        <p>比如客户购买商品后的付款过程。客户余额减少，订单表状态更改，同时要写入客户消费记录表等等。假如我们仅操作3张表。</p>
                        <p>付款过程肯定要卸载模型中，除了主表外，还有2张附表。</p>
                        <p>对应的模型为OrderModel，方法名称为pay</p>
                        <blockquote>
                            <ol class="linenums">
                                <li><code>class Order extends BaseModel{</code></li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;</span><code>protected $table = 'order';</code> //订单表</li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;</span><code>protected $tableUserBlance = 'user_blance';</code> //客户余额表</li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;</span><code>protected $tableUserLog = 'user_log';</code> //客户日志表</li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;</span></li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;</span><code>public function pay($userid, $orderid){</code> //参数分别为用户id和订单id</li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;</span><code>$orderInfo = $this->getOrderInfoByID($orderid);</code></li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;</span><code>$this->db->begin();</code></li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;</span><code>$res_order = $this->db->from($this->table)->where('order_id', $orderid)->set(array('order_status'=>'checkout'...)->upate();</code> //订单已付款</li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;</span><code>$res_blance = $this->db->from($this->tableUserBlance)->where("user_id = $userid AND user_blance >= $orderInfo['order_amount']")->setDec('user_blance', $orderInfo['order_amount'])->upate();</code> //用户金额减少</li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;</span><code>$res_log = $this->db->from($this->tableUserLog)->set(array('user_id' => $userid, 'order_id' >= $orderid, "log_content" => "2015-08-05 20:05:03 客户洞主为订单100346付款"))->setDec('user_blance', $orderInfo['order_amount'])->upate();</code> //用户金额减少</li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;</span><code>if ($res_order && $res_blance && $res_log) {</code> //写入日志</li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><code>$this->db->commit();</code> //完成事务</li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><code>return true;</code></li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;</span><code>} else {</code></li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><code>$this->db->rollback();</code> //回滚</li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;</span><code>}</code></li>
                            </ol>
                        </blockquote>
                        <h3>9.7.3 $this->db对象</h3>
                        <p>$this->db可以完成项目中遇到的大部分SQL需求。通过方法链[ACTION CHAIN]来完成SQL的拼装。</p>
                        <ul>
                            <li><code>$this->db->from();</code>  //FROM table</li>
                            <li><code>$this->db->join();</code> // table a JOIN table b</li>
                            <li><code>$this->db->where();</code> //WHERE 条件</li>
                            <li><code>$this->db->whereIn();</code> //WHERE field IN</li>
                            <li><code>$this->db->whereNotIn();</code> //WHERE field NOT In</li>
                            <li><code>$this->db->wehreExist();</code>//WHERE EXISTS</li>
                            <li><code>$this->db->whereLike();</code> //WHERE field LIKE </li>
                            <li><code>$this->db->whereBetweenAnd();</code> //WHERE field BETWEEN a AND b</li>
                            <li><code>$this->db->whereOr();</code> //WHERE 条件1 OR 条件2</li>
                            <li><code>$this->db->whereConcatLike();</code> //WHERE CONCAT(field_a, field_b) LIKE</li>
                            <li><code>$this->db->whereMore();</code> WHERE field >= </li>
                            <li><code>$this->db->whereLess();</code> WHERE field >= </li>
                            <li><code>$this->db->set();</code> WHERE field <= </li>
                            <li><code>$this->db->setAdd();</code> WHERE field = field + n</li>
                            <li><code>$this->db->setDec();</code> WHERE field = field - n</li>
                            <li><code>$this->db->groupby();</code> GROUP BY field</li>
                            <li><code>$this->db->orderby();</code> ORDER BY field</li>
                            <li><code>$this->db->limit();</code> LIMIT 10 OFFSET 5</li>
                            <li><code>$this->db->page();</code> 分页参数到LIMIT 参数</li>
                            <li><code>$this->db->select();</code> SELECT field</li>
                            <li><code>$this->db->update();</code> UPDATE TABLE SET...</li>
                            <li><code>$this->db->delete();</code> DELETE FROM TABLE...</li>
                            <li><code>$this->db->getLastSqls();</code> 最近查询的SQL</li>
                            <li><code>$this->db->ttl();</code> 缓存结果集</li>
                            <li><code>$this->db->begin();</code> 开始事务</li>
                            <li><code>$this->db->rollback();</code> 回滚事务</li>
                            <li><code>$this->db->commit();</code> 事务完成</li>
                        </ul>
                        <p>详细用法，请参考<em>/Appplication/KantPHP/Database/DbQueryAbstract.php</em></p>
                    </div>
                    <div class="help-post" id="cookie">
                        <h2>10. COOKIE</h2>
                        <p>KantPHP Framework在基类中封装了Cookie方法,可用于生成加密的COOKIE，以及获取解密后的COOKIE。由于是基类方法，可以在控制器和模型中使用。</p>
                        <h3>10.1 配置</h3>
                        <p>打开配置文件，转到cookie项</p>
                        <blockquote>
                            <ol class="linenums">

                                <li><code>'cookie_path' => '/',</code> //Cookie路径</li>
                                <li><code>'cookie_pre' => 'kantphp_',</code> //Cookie前缀</li>
                                <li><code>'cookie_ttl' => 0,</code> //Cookie生存时间</li>
                                <li><code>'auth_key' => 'NMa1FcQBE1HHHd4AQyTV'</code> //<b>重要。Cookie加密的密钥，项目开始前一定要更改。</b></li>
                                <li><code>'cookie_domain' => '',</code> //Cookie作用域</li>
                            </ol>
                        </blockquote>
                        <h3>10.2 用法</h3>
                        <p>设置Cookie</p>
                        <p><code>$this->cookie->set('myname', 'dongzhu');</code> //$_COOKIE['myname']为base64字符串</p>
                        <p>获取Cookie</p>
                        <p><code>$myname = $this->cookie->get('myname');</code> //获取解密后COOKIE。</p>
                    </div>
                    <div class="help-post" id="session">
                        <h2>11. SESSION</h2>
                        <p>KantPHP Framework在基类中封装了SESSION方法。SESSION可选则原生态、保存到指定文件、保存到SqLite。其他保存到MySQL，PostgreSQL等数据库或者Memcache,Redis请开发者参考实例完成。原理是通过PHP的 session_set_save_handler 自定义SESSON保存对象。</p>
                        <h3>11.1 配置</h3>
                        <p>打开配置文件，转到session项。$_SESSION默认读取 defautl 的配置项，即$config['session']['default']。</p>
                        <blockquote>
                            <ol class="linenums">
                                <li><code>'default' => array(</code></li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;</span><code>'type' => 'original',</code> //原生态SESSION</li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;</span><code>'maxlifetime' => 1800,</code> //Session生命周期</li>
                                <li><code>),</code></li>
                                <li><code>'file' => array(</code></li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;</span><code>'type' => 'file',</code> //Session保存到指定文件</li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;</span><code>'maxlifetime' => 1800,</code> //Session生命周期</li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;</span><code>auth_key' => 'NMa1FcQBE1HHHd4AQyTV'</code> //Session加密密钥</li>
                                <li><code>),</code></li>
                                <li><code>'sqlite' => array(</code></li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;</span><code>'type' => 'sqlite',</code></li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;</span><code>'maxlifetime' => 1800,</code></li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;</span><code>'auth_key' => 'NMa1FcQBE1HHHd4AQyTV'</code> //Session加密密钥</li>
                                <li><code>)</code></li>
                            </ol>
                        </blockquote>
                        <p>已有3种模式的例子。选择与你的Session模式，改动下配置参数就行。</p>
                        <h3>11.2 用法</h3>
                        <p>Sessiion已经开启，不要再继续使用session_start()。像原生态PHP设置和获取Session一样操作。如</p>
                        <p><code>$_SESSION['myname'] = 'dongzhu';</code>。</p>
                        <p><code>$myname = $_SESSION['myname'];</code>。</p>
                    </div>
                    <div class="help-post" id="cache">
                        <h2>12. CACHE</h2>
                        <p>数据的缓存功能。KantPHP Framework在基类中集成了Cahche方法。在之前的 <a href="#controller">7.控制器</a> 章节中已经提及过。</p>
                        <h3>12.1 配置</h3>
                        <p>打开配置文件，转到cache项。缓存支持保存到文件，Memcache, Redis。Cache初始化时，读取的是 default 配置项$config['cache']['deault']。</p>
                        <blockquote>
                            <ol class="linenums">
                                <li><code>'default' => array(</code></li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;</span><code>'type' => 'file'</code> //文件缓存</li>
                                <li><code>),</code></li>
                                <li><code>'file' => array(</code></li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;</span><code>'type' => 'memcache',</code> //Memcache缓存</li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;</span><code>'hostname' => 'localhost',</code> //Memcache主机名</li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;</span><code>'port' => 11211</code> //Memcache端口号</li>
                                <li><code>),</code></li>
                                <li><code>'sqlite' => array(</code></li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;</span><code>'type' => 'redis',</code> //Redis缓存</li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;</span><code>'hostname' => 'localhost',</code> //Redis主机名</li>
                                <li><span class="pln">&nbsp;&nbsp;&nbsp;&nbsp;</span><code>'port' => 6379</code> //Redis端口号</li>
                                <li><code>)</code></li>
                            </ol>
                        </blockquote>
                        <h3>12.2 用法</h3>
                        <blockquote>
                            <ol class="linenums">
                                <li><code>$this->cache->set('var', 'hello world); </code> 缓存字符串'hello world'，查找的键为'var'。</li>
                                <li><code>$this->cache->get('var');</code> 查找键为'var'的缓存内容。</li>
                            </ol>
                        </blockquote>
                    </div>
                    <div class="help-post" id="rewrite">
                        <h2>13. 路由与重写</h2>
                        <p>URL地址重写，有几大优点：1.美化URL。2.利于搜索引擎优化。3.提高安全性，避免暴露复杂的参数。</p>
                        <p>URL地址重写，可以通过Apache，Nginx来填写规则。</p>
                        <p>KantPHP Framework自带路由重写规则，把URL地址与特定的路由匹配。配置灵活方便。如：</p>
                        <blockquote>
                            <p>当用户输入<em>/topic/1002.html</em>时，实际访问地址为：<em>/blog/detail/index/id,1002.html</em></p>
                        </blockquote>
                        <h3>13.1 配置</h3>
                        <p>打开配置文件,转到route_rules项。数组中追加一个键值对即可。</p>
                        <blockquote>
                            <ol class="linenums">
                                <li><code>'|topic/(\d+)+|i' => 'blog/detail/index/id,$1',</code> //key值为被重写后的url地址，value值为真实的url地址。正则界定符可以用||，也可以用//，为避免闹心的转义，建议用||。i是正则修正符。i表示不区分大小写。</li>
                            </ol>
                        </blockquote>
                        
                    </div>
                    <div class="help-post" id="extend">
                        <h2>14. 扩展</h2>
                        <p>KantPHP Framework定位于轻量级框架，扩展性一般，预留扩展接口较少，但并非不可扩展，只是需要修改或重写底层代码。KantPHP Framework 采用BSD 3-Clause license协议， 对开发者约束是非常宽松的。代码量体裁衣，适合就行。</p>
                        <p>例如如你觉得原生态模板引擎太OUT了，想载入Smarty模板。你则需要修改<em>/Application/Kantphp/View/View.php</em></p>
                        <p>例如你想扩展BaseController方法，则需要修改<em>/Application/Kantphp/Controller/BaseController.php</em></p>
                        <p>KantPHP Framework的代码结构清，即使不看说明文档，也可以轻车熟路的修改。</p>
                        <p>如果在扩展中有遇到问题，欢迎给洞主发送邮件zhenqiang.zhang@hotmail.com。</p>
                    </div>
                    <div class="help-post" id="thirdparty">
                        <h2>15. 第三方类库</h2>
                        <p>第三方类库地址位于<em>/Application/Library</em></p>
                        <p>15.1 使用方法</p>
                        <blockquote>
                            <ol>
                                <li>引入并初始化。<code>require "Page.php"; $PageObj = new Page([参数...]);</code></li>
                                <li>$this->library() 方法。这需要文件名和类名一致。</li>
                            </ol>
                        </blockquote>
                    </div>
                    <div class="help-psot" id="end">
                        <p>文档至此结束。【3.架构原理】留白，短期不完善。与很多框架原理类似，不想老调重弹。</p>
                        <p>获取Pathinfo或者URL参数，进行调度。然后反射控制器，渲染视图，输出到浏览器。</p>
                    </div>
                </div>

            </div><!-- /.container -->

            <footer class="help-footer">
                <p>Copyright By<a href="http://www.kantphp.com"> KantPHP Framework Studio </a></p>
                <p>
                    <a href="#">Back to top</a>
                </p>
            </footer>
    </body>
</html>