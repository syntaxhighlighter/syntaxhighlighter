;(function()
{
	// CommonJS
	SyntaxHighlighter = SyntaxHighlighter || (typeof require !== 'undefined'? require('shCore').SyntaxHighlighter : null);

	function Brush()
	{
		// Swift brush contributed by Nate Cook
		// http://natecook.com/code/swift-syntax-highlighting
		
		function getKeywordsPrependedBy(keywords, by)
		{
			return '(?:' + keywords.replace(/^\s+|\s+$/g, '').replace(/\s+/g, '|' + by + '\\b').replace(/^/, by + '\\b') + ')\\b';
		}
		
		function multiLineCCommentsAdd(match, regexInfo) 
		{
			var str = match[0], result = [], pos = 0, matchStart = 0, level = 0;
			
			while (pos < str.length - 1) {
				var chunk = str.substr(pos, 2);
				if (level == 0) {
					if (chunk == "/*") {
						matchStart = pos;
						level++;
						pos += 2;
					} else {
						pos++;
					}
				} else {
					if (chunk == "/*") {
						level++;
						pos += 2;
					} else if (chunk == "*/") {
						level--;
						if (level == 0) {
							result.push(new SyntaxHighlighter.Match(str.substring(matchStart, pos + 2), matchStart + match.index, regexInfo.css));
						}
						pos += 2;
					} else {
						pos++;					
					}
				}
			}
			
			return result;
		}
		
		function stringAdd(match, regexInfo)
		{
			var str = match[0], result = [], pos = 0, matchStart = 0, level = 0;
			
			while (pos < str.length - 1) {
				if (level == 0) {
					if (str.substr(pos, 2) == "\\(") {
						result.push(new SyntaxHighlighter.Match(str.substring(matchStart, pos + 2), matchStart + match.index, regexInfo.css));
						level++;
						pos += 2;
					} else {
						pos++;
					}
				} else {
					if (str[pos] == "(") {
						level++;
					}
					if (str[pos] == ")") {
						level--;
						if (level == 0) {
							matchStart = pos;
						}
					}
					pos++;
				}
			}
			if (level == 0) {
				result.push(new SyntaxHighlighter.Match(str.substring(matchStart, str.length), matchStart + match.index, regexInfo.css));
			}
			
			return result;
		};
		
		// "Swift-native types" are all the protocols, classes, structs, enums, funcs, vars, and typealiases built into the language
		var swiftTypes = 'AbsoluteValuable Any AnyClass Array ArrayBound ArrayBuffer ArrayBufferType ArrayLiteralConvertible ArrayType AutoreleasingUnsafePointer BidirectionalIndex Bit BitwiseOperations Bool C CBool CChar CChar16 CChar32 CConstPointer CConstVoidPointer CDouble CFloat CInt CLong CLongLong CMutablePointer CMutableVoidPointer COpaquePointer CShort CSignedChar CString CUnsignedChar CUnsignedInt CUnsignedLong CUnsignedLongLong CUnsignedShort CVaListPointer CVarArg CWideChar Character CharacterLiteralConvertible Collection CollectionOfOne Comparable ContiguousArray ContiguousArrayBuffer DebugPrintable Dictionary DictionaryGenerator DictionaryIndex DictionaryLiteralConvertible Double EmptyCollection EmptyGenerator EnumerateGenerator Equatable ExtendedGraphemeClusterLiteralConvertible ExtendedGraphemeClusterType ExtensibleCollection FilterCollectionView FilterCollectionViewIndex FilterGenerator FilterSequenceView Float Float32 Float64 Float80 FloatLiteralConvertible FloatLiteralType FloatingPointClassification FloatingPointNumber ForwardIndex Generator GeneratorOf GeneratorOfOne GeneratorSequence Hashable HeapBuffer HeapBufferStorage HeapBufferStorageBase ImplicitlyUnwrappedOptional IndexingGenerator Int Int16 Int32 Int64 Int8 IntEncoder IntMax Integer IntegerArithmetic IntegerLiteralConvertible IntegerLiteralType Less LifetimeManager LogicValue MapCollectionView MapSequenceGenerator MapSequenceView MaxBuiltinFloatType MaxBuiltinIntegerType Mirror MirrorDisposition MutableCollection MutableSliceable ObjectIdentifier OnHeap Optional OutputStream PermutationGenerator Printable QuickLookObject RandomAccessIndex Range RangeGenerator RawByte RawOptionSet RawRepresentable Reflectable Repeat ReverseIndex ReverseRange ReverseRangeGenerator ReverseView Sequence SequenceOf SignedInteger SignedNumber Sink SinkOf Slice SliceBuffer Sliceable StaticString Streamable StridedRangeGenerator String StringElement StringInterpolationConvertible StringLiteralConvertible StringLiteralType UInt UInt16 UInt32 UInt64 UInt8 UIntMax UTF16 UTF32 UTF8 UWord UnicodeCodec UnicodeScalar Unmanaged UnsafeArray UnsafePointer UnsignedInteger Void Word Zip2 ZipGenerator2 abs advance alignof alignofValue assert bridgeFromObjectiveC bridgeFromObjectiveCUnconditional bridgeToObjectiveC bridgeToObjectiveCUnconditional c contains count countElements countLeadingZeros debugPrint debugPrintln distance dropFirst dropLast dump encodeBitsAsWords enumerate equal false filter find getBridgedObjectiveCType getVaList indices insertionSort isBridgedToObjectiveC isBridgedVerbatimToObjectiveC isUniquelyReferenced join lexicographicalCompare map max maxElement min minElement nil numericCast partition posix print println quickSort reduce reflect reinterpretCast reverse roundUpToAlignment sizeof sizeofValue sort split startsWith strideof strideofValue swap swift toString transcode true underestimateCount unsafeReflect withExtendedLifetime withObjectAtPlusZero withUnsafePointer withUnsafePointerToObject withUnsafePointers withVaList';
		
		var keywords =	'as break case class continue default deinit do dynamicType else enum ' +
						'extension fallthrough for func if import in init is let new protocol ' + 
						'return self Self static struct subscript super switch Type typealias ' +
						'var where while __COLUMN__ __FILE__ __FUNCTION__ __LINE__ associativity ' +
						'didSet get infix inout left mutating none nonmutating operator override ' +
						'postfix precedence prefix right set unowned unowned(safe) unowned(unsafe) weak willSet';		
		
		var attributes = 'assignment class_protocol exported final lazy noreturn NSCopying NSManaged objc optional required auto_closure noreturn IBAction IBDesignable IBInspectable IBOutlet infix prefix postfix';
		
		
		this.regexList = [
			// html entities
			{ regex: new RegExp('\&[a-z]+;', 'gi'),	css: 'plain' },
			// one line comments
			{ regex: SyntaxHighlighter.regexLib.singleLineCComments, css: 'comments' },
			// multiline comments
			{ regex: new RegExp('\\/\\*[\\s\\S]*\\*\\/', 'g'), css: 'comments', func: multiLineCCommentsAdd },
			// strings
			{ regex: SyntaxHighlighter.regexLib.doubleQuotedString, css: 'string', func: stringAdd },
			// numbers (decimal, hex, binary, octal)
			{ regex: new RegExp('\\b([\\d_]+(\\.[\\de_]+)?|0x[a-f0-9_]+(\\.[a-f0-9p_]+)?|0b[01_]+|0o[0-7_]+)\\b', 'gi'), css: 'value' },
			// Swift keywords
			{ regex: new RegExp(this.getKeywords(keywords), 'gm'), css: 'keyword' },
			// Swift @attributes	
			{ regex: new RegExp(getKeywordsPrependedBy(attributes, '@'), 'gm'), css: 'color1' },
			// Swift-native types
			{ regex: new RegExp(this.getKeywords(swiftTypes), 'gm'), css: 'color2' },
			// user-defined variables, functions, etc.
			{ regex: new RegExp('\\b([a-zA-Z_][a-zA-Z0-9_]*)\\b', 'gi'), css: 'variable' },
			];
	};

	Brush.prototype	= new SyntaxHighlighter.Highlighter();
	Brush.aliases	= ['swift'];

	SyntaxHighlighter.brushes.Swift = Brush;

	// CommonJS
	typeof(exports) != 'undefined' ? exports.Brush = Brush : null;
})();




