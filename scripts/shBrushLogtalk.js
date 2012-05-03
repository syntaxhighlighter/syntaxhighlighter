;(function()
{
	// CommonJS
	typeof(require) != 'undefined' ? SyntaxHighlighter = require('shCore').SyntaxHighlighter : null;

	function Brush()
	{
		// Contributed by Paulo Moura (http://logtalk.org/); last revised on May 3, 2012

		this.regexList = [
		  // variables
			{ regex: new RegExp("[A-Z_][a-zA-Z0-9_]*", 'g'), css: 'variable' },
			// comments
			{ regex: new RegExp("\\%.+", 'gm'), css: 'comments' },
			{ regex: SyntaxHighlighter.regexLib.multiLineCComments, css: 'comments' },
			// strings and atoms
			{ regex: SyntaxHighlighter.regexLib.doubleQuotedString, css: 'string' },
			{ regex: SyntaxHighlighter.regexLib.singleQuotedString, css: 'string' },
			// numbers
			{ regex: new RegExp("0\'.|0b[0-1]+|0o[0-7]+|0x[0-9a-fA-F]+|[-+]?[0-9]+(\.[0-9]+)?([eE]([-+])?[0-9]+)?", 'gi'), css: 'value' },
      // entity creation and abolishing built-in predicates
      { regex: new RegExp("\\b(abolish|c(urrent|reate))_(object|protocol|category)(?=[(])", 'g'), css: 'keyword'},
      // entity property built-in predicates
      { regex: new RegExp("\\b(object|protocol|category)_property(?=[(])", 'g'), css: 'keyword'},
      // entity relation built-in predicates
      { regex: new RegExp("\\bco(mplements_object|nforms_to_protocol)(?=[(])", 'g'), css: 'keyword'},
      { regex: new RegExp("\\bextends_(object|protocol|category)(?=[(])", 'g'), css: 'keyword'},
      { regex: new RegExp("\\bimp(lements_protocol|orts_category)(?=[(])", 'g'), css: 'keyword'},
      { regex: new RegExp("\\b(instantiat|specializ)es_class(?=[(])", 'g'), css: 'keyword'},
      // event built-in predicates
      { regex: new RegExp("\\b(current_event|(abolish|define)_events)(?=[(])", 'g'), css: 'keyword'},
      // flag built-in predicates
      { regex: new RegExp("\\b(current|set)_logtalk_flag(?=[(])", 'g'), css: 'keyword'},
      { regex: new RegExp("\\b(set|current)_prolog_flag(?=[(])", 'g'), css: 'keyword'},
      // compiling and loading built-in predicates
      { regex: new RegExp("\\blogtalk_(compile|l(oad|oad_context|ibrary_path))(?=[(])", 'g'), css: 'keyword'},
      // event handler methods
      { regex: new RegExp("\\b(after|before)(?=[(])", 'g'), css: 'keyword'},
      // execution-context methods
      { regex: new RegExp("\\b(parameter|this|se(lf|nder))(?=[(])", 'g'), css: 'keyword'},
      // reflection methods
      { regex: new RegExp("\\b(current_predicate|predicate_property)(?=[(])", 'g'), css: 'keyword'},
      // term and goal expansion methods
      { regex: new RegExp("\\b(expand_(goal|term)|(goal|term)_expansion|phrase)(?=[(])", 'g'), css: 'keyword'},
      // database methods
      { regex: new RegExp("\\b(clause|retract(all)?)(?=[(])", 'g'), css: 'keyword'},
      { regex: new RegExp("\\ba(bolish|ssert(a|z))(?=[(])", 'g'), css: 'keyword'},
      // all solution methods
      { regex: new RegExp("\\b((bag|set)of|f(ind|or)all)(?=[(])", 'g'), css: 'keyword'},
      // multi-threading built-in predicates
      { regex: new RegExp("\\bthreaded(_(call|once|ignore|exit|peek|wait|notify))?(?=[(])", 'g'), css: 'keyword'},
      // term unification built-in predicates
      { regex: new RegExp("\\b(subsumes_term|unify_with_occurs_check)(?=[(])", 'g'), css: 'keyword'},
      // term creation and decomposition built-in predicates
      { regex: new RegExp("\\b(functor|arg|copy_term|numbervars|term_variables)(?=[(])", 'g'), css: 'keyword'},
      // evaluable functors
      { regex: new RegExp("\\b(rem|m(ax|in|od)|abs|sign)(?=[(])", 'g'), css: 'keyword'},
      { regex: new RegExp("\\b(float_(integer|fractional)_part|float)(?=[(])", 'g'), css: 'keyword'},
      { regex: new RegExp("\\b(floor|truncate|round|ceiling)(?=[(])", 'g'), css: 'keyword'},
      { regex: new RegExp("\\b(cos|a(cos|sin|tan)|exp|log|s(in|qrt))(?=[(])", 'g'), css: 'keyword'},
      { regex: new RegExp("\\b(e|pi|is|rem|mod)\\b", 'g'), css: 'keyword'},
      // term type built-in predicates
      { regex: new RegExp("\\b(var|atom(ic)?|integer|float|c(allable|ompound)|n(onvar|umber)|ground|acyclic_term)(?=[(])", 'g'), css: 'keyword'},
      // term comparison built-in predicates
      { regex: new RegExp("\\bcompare(?=[(])", 'g'), css: 'keyword'},
      // stream selection and control built-in predicates
      { regex: new RegExp("\\b(curren|se)t_(in|out)put(?=[(])", 'g'), css: 'keyword'},
      { regex: new RegExp("\\b(open|close)(?=[(])", 'g'), css: 'keyword'},
      { regex: new RegExp("\\bflush_output(?=[(])", 'g'), css: 'keyword'},
      { regex: new RegExp("\\b(flush_output|at_end_of_stream)\\b", 'g'), css: 'keyword'},
      { regex: new RegExp("\\b(stream_property|at_end_of_stream|set_stream_position)(?=[(])", 'g'), css: 'keyword'},
      // character and byte input/output built-in predicates
      { regex: new RegExp("\\b(get|p(eek|ut))_(byte|c(har|ode))(?=[(])", 'g'), css: 'keyword'},
      { regex: new RegExp("\\bnl(?=[(])", 'g'), css: 'keyword'},
      { regex: new RegExp("\\b(nl)\\b", 'g'), css: 'keyword'},
      // term input/output built-in predicates
      { regex: new RegExp("\\b(read(_term)?)(?=[(])", 'g'), css: 'keyword'},
      { regex: new RegExp("\\b(write(q|_(canonical|term))?)(?=[(])", 'g'), css: 'keyword'},
      { regex: new RegExp("\\b((current_)?char_conversion)(?=[(])", 'g'), css: 'keyword'},
      // operator built-in predicates
      { regex: new RegExp("\\b(op|current_op)(?=[(])", 'g'), css: 'keyword'},
      // atomic term processing built-in predicates
      { regex: new RegExp("\\batom_(length|c(hars|o(ncat|des)))(?=[(])", 'g'), css: 'keyword'},
      { regex: new RegExp("\\b(char_code|sub_atom)(?=[(])", 'g'), css: 'keyword'},
      { regex: new RegExp("\\bnumber_(c(har|ode)s)(?=[(])", 'g'), css: 'keyword'},
      { regex: new RegExp("\\bhalt(?=[(])", 'g'), css: 'keyword'},
      { regex: new RegExp("\\b(halt)\\b", 'g'), css: 'keyword'},
      // sorting built-in predicates
      { regex: new RegExp("\\b((key)?sort)(?=[(])", 'g'), css: 'keyword'},
      // built-in control constructs
      { regex: new RegExp("\\b(ca(ll|tch)|throw)(?=[(])", 'g'), css: 'keyword'},
      { regex: new RegExp("\\b(true|fail|repeat)\\b", 'g'), css: 'keyword'},
      { regex: new RegExp("\\b(ignore|once)(?=[(])", 'g'), css: 'keyword'},
      // directives
			{ regex: new RegExp("^\\s*:\-\\s(c(a(lls|tegory)|oinductive)|p(ublic|r(ot(ocol|ected)|ivate))|e(l(if|se)|n(coding|sure_loaded)|xport)|i(f|n(clude|itialization|fo))|alias|d(ynamic|iscontiguous)|m(eta_(non_terminal|predicate)|od(e|ule)|ultifile)|reexport|s(et_(logtalk|prolog)_flag|ynchronized)|o(bject|p)|use(s|_module))(?=[(])", 'gm'), css: 'preprocessor' },
			{ regex: new RegExp("^\\s*:\-\\s(e(lse|nd(if|_(category|object|protocol)))|dynamic|synchronized|threaded)\.", 'gm'), css: 'preprocessor' },
			{ regex: new RegExp("^\\s*(complements|extends|i(nstantiates|mp(lements|orts))|specializes)(?=[(])", 'gm'), css: 'preprocessor' },
      // atoms
      { regex: new RegExp("\\b[a-z][A-Za-z0-9_]*", 'g'), css: 'plain'},
      // Logtalk message sending operators
			{ regex: new RegExp("(::|\\^\\^|<<|:)", 'g'), css: 'constants'},
      // Logtalk external-call control construct
			{ regex: new RegExp("(\\{|\\})", 'g'), css: 'constants'},
      // Prolog operators
			{ regex: new RegExp("(\\\\\+|\\-\\->|\\->|=\\.\\.|;|!|:\-|\\^)", 'g'), css: 'constants'},
      // Prolog arithmetic operators
			{ regex: new RegExp("(//|[+\\-]|/|\\*|\\*\\*)", 'g'), css: 'constants'},
      // Prolog arithmetic comparison operators
			{ regex: new RegExp("(=<|<|=:=|=\\\\=|>=|>)", 'g'), css: 'constants'},
      // Prolog term comparison operators
			{ regex: new RegExp("(@<|@=<|==|\\\\==|@>|@>=)", 'g'), css: 'constants'},
      // Prolog unification operators
			{ regex: new RegExp("(=|\\\\=)", 'g'), css: 'constants'},
      // Logtalk mode operators
			{ regex: new RegExp("([?]|@)", 'g'), css: 'constants'},
      // Prolog bitwise operators
			{ regex: new RegExp("(>>|/\\\\|\\\\/|\\\\)", 'g'), css: 'constants'}

			];
	};

	Brush.prototype	= new SyntaxHighlighter.Highlighter();
	Brush.aliases	= ['lgt', 'logtalk', 'Logtalk'];

	SyntaxHighlighter.brushes.Logtalk = Brush;

	// CommonJS
	typeof(exports) != 'undefined' ? exports.Brush = Brush : null;
})();
