
var ApiGen = ApiGen || {};
ApiGen.elements = [["f","array_end()"],["f","array_head()"],["f","array_rest()"],["c","Nethgui\\Adapter\\AdapterAggregateInterface"],["c","Nethgui\\Adapter\\AdapterAggregationInterface"],["c","Nethgui\\Adapter\\AdapterInterface"],["c","Nethgui\\Adapter\\ArrayAdapter"],["c","Nethgui\\Adapter\\LazyLoaderAdapter"],["c","Nethgui\\Adapter\\ModifiableInterface"],["c","Nethgui\\Adapter\\MultipleAdapter"],["c","Nethgui\\Adapter\\RecordAdapter"],["c","Nethgui\\Adapter\\RecordKeyAdapter"],["c","Nethgui\\Adapter\\ScalarAdapter"],["c","Nethgui\\Adapter\\TableAdapter"],["c","Nethgui\\Adapter\\TabularValueAdapter"],["c","Nethgui\\Authorization\\AccessControlResponseInterface"],["c","Nethgui\\Authorization\\AuthorizationAttributesProviderInterface"],["c","Nethgui\\Authorization\\AuthorizedModuleSet"],["c","Nethgui\\Authorization\\JsonPolicyDecisionPoint"],["c","Nethgui\\Authorization\\LazyAccessControlResponse"],["c","Nethgui\\Authorization\\PolicyDecisionPointInterface"],["c","Nethgui\\Authorization\\PolicyEnforcementPointInterface"],["c","Nethgui\\Authorization\\PolicyRule"],["c","Nethgui\\Authorization\\User"],["c","Nethgui\\Authorization\\UserInterface"],["c","Nethgui\\Component\\DependencyConsumer"],["c","Nethgui\\Component\\DependencyInjectorAggregate"],["c","Nethgui\\Controller\\AbstractController"],["c","Nethgui\\Controller\\Collection\\AbstractAction"],["c","Nethgui\\Controller\\Collection\\ActionInterface"],["c","Nethgui\\Controller\\CollectionController"],["c","Nethgui\\Controller\\CompositeController"],["c","Nethgui\\Controller\\ListComposite"],["c","Nethgui\\Controller\\NullRequest"],["c","Nethgui\\Controller\\RequestHandlerInterface"],["c","Nethgui\\Controller\\RequestInterface"],["c","Nethgui\\Controller\\RequestTest"],["c","Nethgui\\Controller\\Table\\AbstractAction"],["c","Nethgui\\Controller\\Table\\Help"],["c","Nethgui\\Controller\\Table\\Modify"],["c","Nethgui\\Controller\\Table\\PluggableAction"],["c","Nethgui\\Controller\\Table\\PluginCollector"],["c","Nethgui\\Controller\\Table\\Read"],["c","Nethgui\\Controller\\Table\\RowAbstractAction"],["c","Nethgui\\Controller\\Table\\RowPluginAction"],["c","Nethgui\\Controller\\TableController"],["c","Nethgui\\Controller\\TabsController"],["c","Nethgui\\Controller\\ValidationReportInterface"],["c","Nethgui\\Exception\\AuthorizationException"],["c","Nethgui\\Exception\\HttpException"],["c","Nethgui\\Framework"],["c","Nethgui\\Log\\AbstractLog"],["c","Nethgui\\Log\\LogConsumerInterface"],["c","Nethgui\\Log\\LogInterface"],["c","Nethgui\\Log\\Nullog"],["c","Nethgui\\Log\\Syslog"],["c","Nethgui\\Model\\StaticFiles"],["c","Nethgui\\Model\\SystemTasks"],["c","Nethgui\\Model\\UserNotifications"],["c","Nethgui\\Model\\ValidationErrors"],["c","Nethgui\\Module\\AbstractModule"],["c","Nethgui\\Module\\Composite"],["c","Nethgui\\Module\\CompositeModuleAttributesProvider"],["c","Nethgui\\Module\\Help"],["c","Nethgui\\Module\\Help\\Common"],["c","Nethgui\\Module\\Help\\Read"],["c","Nethgui\\Module\\Help\\Renderer"],["c","Nethgui\\Module\\Help\\Show"],["c","Nethgui\\Module\\Help\\Template"],["c","Nethgui\\Module\\Help\\Widget"],["c","Nethgui\\Module\\Language"],["c","Nethgui\\Module\\Login"],["c","Nethgui\\Module\\Logout"],["c","Nethgui\\Module\\Main"],["c","Nethgui\\Module\\Menu"],["c","Nethgui\\Module\\ModuleAttributesInterface"],["c","Nethgui\\Module\\ModuleCompositeInterface"],["c","Nethgui\\Module\\ModuleInterface"],["c","Nethgui\\Module\\ModuleLoader"],["c","Nethgui\\Module\\ModuleSetInterface"],["c","Nethgui\\Module\\Notification"],["c","Nethgui\\Module\\Notification\\AbstractNotification"],["c","Nethgui\\Module\\Resource"],["c","Nethgui\\Module\\SimpleModuleAttributesProvider"],["c","Nethgui\\Module\\SystemModuleAttributesProvider"],["c","Nethgui\\Module\\Tracker"],["c","Nethgui\\Renderer\\AbstractRenderer"],["c","Nethgui\\Renderer\\Json"],["c","Nethgui\\Renderer\\ReadonlyView"],["c","Nethgui\\Renderer\\TemplateRenderer"],["c","Nethgui\\Renderer\\WidgetFactoryInterface"],["c","Nethgui\\Renderer\\WidgetInterface"],["c","Nethgui\\Renderer\\Xhtml"],["c","Nethgui\\Serializer\\ArrayAccessSerializer"],["c","Nethgui\\Serializer\\KeySerializer"],["c","Nethgui\\Serializer\\PropSerializer"],["c","Nethgui\\System\\AlwaysFailValidator"],["c","Nethgui\\System\\CallbackValidator"],["c","Nethgui\\System\\DatabaseInterface"],["c","Nethgui\\System\\MandatoryValidatorInterface"],["c","Nethgui\\System\\NethPlatform"],["c","Nethgui\\System\\PlatformConsumerInterface"],["c","Nethgui\\System\\PlatformInterface"],["c","Nethgui\\System\\Process"],["c","Nethgui\\System\\ProcessInterface"],["c","Nethgui\\System\\SessionDatabase"],["c","Nethgui\\System\\Validator"],["c","Nethgui\\System\\ValidatorInterface"],["c","Nethgui\\Test\\Tool\\DB"],["c","Nethgui\\Test\\Tool\\MockFactory"],["c","Nethgui\\Test\\Tool\\MockObject"],["c","Nethgui\\Test\\Tool\\MockState"],["c","Nethgui\\Test\\Tool\\PermissivePolicyDecisionPoint"],["c","Nethgui\\Test\\Tool\\StaticPolicyDecisionPoint"],["c","Nethgui\\Test\\Unit\\Nethgui\\Adapter\\ArrayAdapterTest"],["c","Nethgui\\Test\\Unit\\Nethgui\\Adapter\\MultipleAdapterTest"],["c","Nethgui\\Test\\Unit\\Nethgui\\Adapter\\ParameterSet\\EmptyTest"],["c","Nethgui\\Test\\Unit\\Nethgui\\Adapter\\ParameterSet\\WithAdaptersTest"],["c","Nethgui\\Test\\Unit\\Nethgui\\Adapter\\RecordAdapterTest"],["c","Nethgui\\Test\\Unit\\Nethgui\\Adapter\\RecordAdapterTester"],["c","Nethgui\\Test\\Unit\\Nethgui\\Adapter\\RecordKeyAdapterTest"],["c","Nethgui\\Test\\Unit\\Nethgui\\Adapter\\ScalarAdapterTest"],["c","Nethgui\\Test\\Unit\\Nethgui\\Adapter\\TableAdapter1Test"],["c","Nethgui\\Test\\Unit\\Nethgui\\Adapter\\TableAdapter2Test"],["c","Nethgui\\Test\\Unit\\Nethgui\\Adapter\\TabularValueAdapterDegradedTest"],["c","Nethgui\\Test\\Unit\\Nethgui\\Adapter\\TabularValueAdapterTest"],["c","Nethgui\\Test\\Unit\\Nethgui\\Authorization\\JsonPolicyDecisionPointTest"],["c","Nethgui\\Test\\Unit\\Nethgui\\Authorization\\LazyAccessControlResponseTest"],["c","Nethgui\\Test\\Unit\\Nethgui\\Authorization\\PolicyRuleTest"],["c","Nethgui\\Test\\Unit\\Nethgui\\Authorization\\ResourceX"],["c","Nethgui\\Test\\Unit\\Nethgui\\Authorization\\ResourceY"],["c","Nethgui\\Test\\Unit\\Nethgui\\Authorization\\UserTest"],["c","Nethgui\\Test\\Unit\\Nethgui\\Log\\AbstractLogTest"],["c","Nethgui\\Test\\Unit\\Nethgui\\Log\\NullogTest"],["c","Nethgui\\Test\\Unit\\Nethgui\\Log\\SyslogTest"],["c","Nethgui\\Test\\Unit\\Nethgui\\Module\\AbstractControllerTest"],["c","Nethgui\\Test\\Unit\\Nethgui\\Module\\CompositeTest"],["c","Nethgui\\Test\\Unit\\Nethgui\\Module\\ConcreteCompositeModule1"],["c","Nethgui\\Test\\Unit\\Nethgui\\Module\\ConcreteStandardModule1"],["c","Nethgui\\Test\\Unit\\Nethgui\\Module\\ModuleLoaderTest"],["c","Nethgui\\Test\\Unit\\Nethgui\\Module\\Notification\\TextNotificationBoxTest"],["c","Nethgui\\Test\\Unit\\Nethgui\\Module\\SimpleModuleAttributesProviderTest"],["c","Nethgui\\Test\\Unit\\Nethgui\\Renderer\\HttpCommandReceiverTest"],["c","Nethgui\\Test\\Unit\\Nethgui\\Renderer\\JsonTest"],["c","Nethgui\\Test\\Unit\\Nethgui\\Renderer\\MarshallingReceiverTest"],["c","Nethgui\\Test\\Unit\\Nethgui\\Renderer\\XhtmlTest"],["c","Nethgui\\Test\\Unit\\Nethgui\\Serializer\\ArrayAccessSerializerTest"],["c","Nethgui\\Test\\Unit\\Nethgui\\Serializer\\KeySerializerTest"],["c","Nethgui\\Test\\Unit\\Nethgui\\Serializer\\PropSerializerTest"],["c","Nethgui\\Test\\Unit\\Nethgui\\System\\EsmithDatabaseTest"],["c","Nethgui\\Test\\Unit\\Nethgui\\System\\NethPlatformTest"],["c","Nethgui\\Test\\Unit\\Nethgui\\System\\PhpWrapperExec"],["c","Nethgui\\Test\\Unit\\Nethgui\\System\\SessionDatabaseTest"],["c","Nethgui\\Test\\Unit\\Nethgui\\System\\TestSession"],["c","Nethgui\\Test\\Unit\\Nethgui\\System\\ValidatorTest"],["c","Nethgui\\Test\\Unit\\Nethgui\\Utility\\PamAuthenticatorTest"],["c","Nethgui\\Test\\Unit\\Nethgui\\View\\CommandTest"],["c","Nethgui\\Test\\Unit\\Nethgui\\View\\TranslatorTest"],["c","Nethgui\\Test\\Unit\\Nethgui\\View\\TranslatorTestModule"],["c","Nethgui\\Test\\Unit\\Nethgui\\View\\TranslatorTestPhpWrapper"],["c","Nethgui\\Test\\Unit\\Nethgui\\View\\ViewCommandSequenceTest"],["c","Nethgui\\Test\\Unit\\Nethgui\\View\\ViewGenericTest"],["c","Nethgui\\Test\\Unit\\Test\\Tool\\MockStateTest"],["c","Nethgui\\Utility\\ArrayDisposable"],["c","Nethgui\\Utility\\DisposableInterface"],["c","Nethgui\\Utility\\HttpResponse"],["c","Nethgui\\Utility\\NullSession"],["c","Nethgui\\Utility\\PhpConsumerInterface"],["c","Nethgui\\Utility\\PhpWrapper"],["c","Nethgui\\Utility\\SessionConsumerInterface"],["c","Nethgui\\Utility\\SessionInterface"],["c","Nethgui\\View\\CommandReceiverInterface"],["c","Nethgui\\View\\LegacyCommandBag"],["c","Nethgui\\View\\Translator"],["c","Nethgui\\View\\TranslatorInterface"],["c","Nethgui\\View\\View"],["c","Nethgui\\View\\ViewableInterface"],["c","Nethgui\\View\\ViewInterface"],["c","Nethgui\\Widget\\AbstractWidget"],["c","Nethgui\\Widget\\Xhtml\\Button"],["c","Nethgui\\Widget\\Xhtml\\CheckBox"],["c","Nethgui\\Widget\\Xhtml\\CollectionEditor"],["c","Nethgui\\Widget\\Xhtml\\Columns"],["c","Nethgui\\Widget\\Xhtml\\ElementList"],["c","Nethgui\\Widget\\Xhtml\\ElementModule"],["c","Nethgui\\Widget\\Xhtml\\ElementRenderer"],["c","Nethgui\\Widget\\Xhtml\\Fieldset"],["c","Nethgui\\Widget\\Xhtml\\FieldsetSwitch"],["c","Nethgui\\Widget\\Xhtml\\FileUpload"],["c","Nethgui\\Widget\\Xhtml\\Form"],["c","Nethgui\\Widget\\Xhtml\\Hidden"],["c","Nethgui\\Widget\\Xhtml\\ObjectPicker"],["c","Nethgui\\Widget\\Xhtml\\ObjectsCollection"],["c","Nethgui\\Widget\\Xhtml\\Panel"],["c","Nethgui\\Widget\\Xhtml\\ProgressBar"],["c","Nethgui\\Widget\\Xhtml\\RadioButton"],["c","Nethgui\\Widget\\Xhtml\\Selector"],["c","Nethgui\\Widget\\Xhtml\\Slider"],["c","Nethgui\\Widget\\Xhtml\\Tabs"],["c","Nethgui\\Widget\\Xhtml\\TextArea"],["c","Nethgui\\Widget\\Xhtml\\TextInput"],["c","Nethgui\\Widget\\Xhtml\\TextLabel"],["c","Nethgui\\Widget\\Xhtml\\TextList"],["c","Nethgui\\Widget\\XhtmlWidget"],["c","Test\\Tool\\ArrayKeyExists"],["c","Test\\Tool\\ArrayKeyGet"],["c","Test\\Tool\\ArrayKeyGetRegexp"],["c","Test\\Tool\\ModuleTestCase"],["c","Test\\Tool\\ModuleTestEnvironment"],["c","Test\\Tool\\SystemCommandExecution"],["f","writeHeader()"]];