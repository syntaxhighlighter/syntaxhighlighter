/**
 * SyntaxHighlighter
 * http://alexgorbatchev.com/SyntaxHighlighter
 *
 * SyntaxHighlighter is donationware. If you are using it, please donate.
 * http://alexgorbatchev.com/SyntaxHighlighter/donate.html
 *
 * @version
 * 3.0.83 (July 02 2010)
 * 
 * @copyright
 * Copyright (C) 2004-2010 Alex Gorbatchev.
 *
 * @license
 * Dual licensed under the MIT and GPL licenses.
 *
 * SyntaxHighlighter Biferno brush by Sandro Bilbeisi
 * http://www.sandrobilbeisi.org/
 * Dual licensed under the MIT and GPL licenses.
 * updates: http://www.sandrobilbeisi.org/wp/works/web-development/biferno-javascript-brush-for-syntaxhighlighter-shbrush-js/
 */
;(function()
{
	// CommonJS
	typeof(require) != 'undefined' ? SyntaxHighlighter = require('shCore').SyntaxHighlighter : null;

	function Brush()
	{
		var flowcontrols	=	'exit stop for do while if else break continue switch case default include lock unlock debug goto function class return';

		var reservedkeywords =	'void local global application session persistent var const true false type scope this public private protected static super obj extends';

		var classes		= 'ansi array biferno boolean byte cacheItem char classInfo client collection curApp curFile curScript db double error file float folder header httpPage image imageUtils int jclass long map mapquery memberInfo multipart object paramInfo point ref regexp request search serverInfo short smtp stackItem string time unix unsigned XmlDoc XmlNode';

		var utilClasses	= 'cookie folderExt headerExt httpExt jclassExt mailAttach requestExt smtpExt utils';

		var methods	=	'readline strstr strch strcmp strcspn strncat strncmp strncpy strpbrk strrch strspn random srandom GetElemClass SetElemClass Add SetDim Delete ToString Index Reverse Swap Find Count Min Max Reset SubArray Insert Sort Flush IsDef GetErrorDescription GetIndSID SessionVariable Flush Reload Publish Unpublish GetPubVariable RegisterNewApp Delay GetCustomOutput SetCustomOutput SetStandardOutput GetIndVariable GetIndVariableRef GetTotVariables IsDef Undef GetNumFormat SetNumFormat LaunchProcess ValueOf GetStack Exec Call CallExt Prepare FreePrepare RowSetSize GetPrepared Bind BindAll ExecPrepared GetCurRecs GetAffectedRecs FetchRec Seek Tell Warning Free Escape RealEscape RealUnescape Transaction Commit RollBack GetSpecific SetSpecific LobWrite LobRead Pow Hex Abs Sqr Sqrt Sin Cos Int Round Function State Suspend Resume ThrowException Update Open Close Delete Get Put Append Move Copy Rename Exists Flush CheckPath IsOpen MakeAlias IsAlias IsFolder ResolvePath ResolveAlias fchmod fgetmod symlink NativePath BifernoPath GetNextLine Lock Unlock Create Delete MakeAlias Rename Walk fchmod fgetmod GetField SetField AddField RemoveField Exec Pow Hex Abs Sqr Sqrt Sin Cos Pow Hex Abs Sqr Sqrt Sin Cos IsDef ToFile Hide Show Lock Unlock IsInitialized IsHidden ConstructorString DebuggerString ValueOfInput Create GetTargetInfo Match GetField Redirect SetOption ToSQL SendMail SendMailAsync SendMailFile ParseMailFile GetMXRecords Encode Decode Escape UrlEncode UrlDecode Find Begins Ends Contains ContainsWordBegin ContainsWordEnd ContainsWordExact In Compare UpToLower LowToUpper SubString ToArray IsEMail IsDate IsNumeric Hilite Substitute Zap Pad HTUUEncode HTUUDecode Capitalize RemoveSubString InsertSubString IsANSIStandard Log Hex2Bin Bin2Hex MD5 Hour Date ToSecs Strftime GMT UString In Millisecs getenv putenv setenv unsetenv getuser getgroup ToTime Pow Hex Abs Sqr Sqrt Sin Cos GetIncludeStack GetSuper GetProperty SetProperty GetByName Name ExtSubset NewMixedRoot NewRoot Save ValidateFile GetAttr NewAttr NewChild NewMixedChild NewPI SetTreeFromString new DoThumbnail GetAll GetArray Set SetPersistent SetTemp GetErrMessage SendMailWithAttachments add_attach send set_async set_from set_message_html set_message_txt set_subject set_timeout set_to tostring ExecRemote IsParamDef AddHeader GetHeader SetHeader Alert';

		var properties	= 'name dim os version versionNum home compilationFlags maxUsers poolFactor upSince applications classes functions totExecTime minExecTime maxExecTime lastExecTime averageExecTime hits currentUsers lastAccess userPath ascii persistentAllowed cloneIsNeeded wantDestructor extendedClass implem sourcePath methods properties constants errors descr purpose seeAlso note ipAddress address user password fromUser userAgent name home children childrenHomes classes functions cacheTotItems cacheTotSize cacheItems basePath path curLine fromCache cache basePath timeout currentThreads maxThreads errNum name msg errClass subErr subErrDescr classNote path lineNum fileOffset line table descr resumable lastMultiStrLine path name permission openMode length resForkLength creatTime modifTime isOpen isAlias osType osCreator curLine user group curPos path name openMode creatTime modifTime user group head body name implem sourcePath className memberType returnClass returnAeLevel returnAeClass purpose descr errors seeAlso note returns prototype varArgs nonames isStatic isConst visibility totParams paramName paramClass paramAeLevel paramAeClass paramTarget paramDefault paramDescr data name path contentType target notbol noteol soff eoff contentType method url host filePath physicalPath fileName searchArg referer protocol scheme port tot mode oper group findType domain serverName root filePath line prototype classOwner length char format year month day hour minute second dayOfWeek includeIndex thisObj next null async byRef';

		var utilclasses		= 'cookie headerExt mailAttach smtpExt folderExt httpExt requestExt utils';

		var functions		= 'Eval print includeFile includeFolder';

		//var operators	= '== ||';

		var numregex = '(?&lt;=[^\w\d]|^)(((([0-9]+\.[0-9]*)|(\.[0-9]+))([eE][+\-]?[0-9]+)?[fFlL]?)|((([1-9][0-9]*)|0[0-7]*|(0[xX][0-9a-fA-F]+))(([uU][lL]?)|([lL][uU]?))?))(?=[^\w\d]|$)';

		var constants	= 'APPLICATION_NAME ADMIN_PROTOCOL ADMIN_IP ADMIN_PASSWORD';
//(?&lt;=[^\w\d]|^)(((([0-9]+\.[0-9]*)|(\.[0-9]+))([eE][+\-]?[0-9]+)?[fFlL]?)|((([1-9][0-9]*)|0[0-7]*|(0[xX][0-9a-fA-F]+))(([uU][lL]?)|([lL][uU]?))?))(?=[^\w\d]|$)
		this.regexList = [
			{ regex: SyntaxHighlighter.regexLib.singleLineCComments,	css: 'bfrcomments comments' },			// one line comments
			{ regex: SyntaxHighlighter.regexLib.multiLineCComments,		css: 'bfrcomments comments' },			// multiline comments
			{ regex: SyntaxHighlighter.regexLib.doubleQuotedString,		css: 'bfrstring string' },			// double quoted strings
			{ regex: SyntaxHighlighter.regexLib.singleQuotedString,		css: 'bfrstring string' },			// single quoted strings
			{ regex: /\$\w+/g,											css: 'variable' },			// printout
			{ regex: /(&lt;|<)\?(biferno|=)?|\?(&gt;|>)/g,					css: 'tag' },
			/*
			{ regex: new RegExp(numregex,'gm'),							css: 'value' },			// number value
			*/
			{ regex: new RegExp(this.getKeywords(flowcontrols), 'gm'),		css: 'bfrflowcontrols script' },				// flow controls
			{ regex: new RegExp(this.getKeywords(reservedkeywords), 'gm'),	css: 'bfrreservedkeywords constants' },			// reserved keywords
			{ regex: new RegExp(this.getKeywords(methods), 'gm'),		css: 'bfrmethods functions' },			// common methods
			{ regex: new RegExp(this.getKeywords(properties), 'gm'),		css: 'bfrproperties color3' },			// common properties
			{ regex: new RegExp(this.getKeywords(classes), 'gm'),		css: 'bfrclasses keyword' },			// common properties
			{ regex: new RegExp(this.getKeywords(utilclasses), 'gm'),		css: 'bfrutilclasses keyword' },			// common properties
			{ regex: new RegExp(this.getKeywords(functions), 'gm'),		css: 'bfrfunctions functions' },			// Predefined functions
			{ regex: new RegExp(this.getKeywords(constants), 'gm'),	css: 'bfrconstants constants' }
			];

		this.forHtmlScript({
		left: /(&lt;|<)\?(biferno|=)?/gi,
		right: /\?(&gt;|>)/gi
		});
	//this.forHtmlScript(SyntaxHighlighter.regexLib.phpScriptTags);
		//this.forHtmlScript(SyntaxHighlighter.regexLib.aspScriptTags);
	};

	Brush.prototype	= new SyntaxHighlighter.Highlighter();
	Brush.aliases	= ['biferno'];

	SyntaxHighlighter.brushes.Biferno = Brush;

	// CommonJS
	typeof(exports) != 'undefined' ? exports.Brush = Brush : null;
})();
