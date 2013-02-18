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
 */
;(function()
{
	// CommonJS
	typeof(require) != 'undefined' ? SyntaxHighlighter = require('shCore').SyntaxHighlighter : null;

	function Brush()
	{
		// by Stefan Agner, from Cortex-M3 assembly reference
	
		var armassembly = 'adc adcs add adds add addw adr and ands asr asrs b bfc bfi bic bics bkpt bl blx bx cbnz cbz clrex clz cmn cmp cpsid cpsie dmb dsb eor eors isb it ldm ldmdb ldmea ldmfd ldmia ldr ldrb ldrbt ldrd ldrex ldrexb ldrexh ldrh ldrht ldrsb ldrsbt ldrsh ldrsht ldrt lsl lsls lsr lsrs mla mls mov movs movt movw mov mrs msr mul muls mvn mvns nop orn orns orr orrs pkhtb pkhbt pop push qadd qadd16 qadd8 qasx qdadd qdsub qsax qsub qsub16 qsub8 rbit rev rev16 revsh ror rors rrx rrxs rsb rsbs sadd16 sadd8 sasx sbc sbcs sbfx sdiv sel sev shadd16 shadd8 shasx shsax shsub16 shsub8 smlabb smlabt smlatb smlatt smlad smladx smlal smlalbb smlalbt smlaltb smlaltt smlald smlaldx smlawb smlawt smlsd smlsld smmla smmls smmlr smmul smmulr smuad smulbb smulbt smultb smultt smull smulwb smulwt smusd smusdx ssat ssat16 ssax ssub16 ssub8 stm stmdb stmea stmfd stmia str strb strbt strd strex strexb strexh strh strht strt sub subs sub subw svc sxtab sxtab16 sxtah sxtb16 sxtb sxth tbb tbh teq tst uadd16 uadd8 usax uhadd16 uhadd8 uhasx uhsax uhsub16 uhsub8 ubfx udiv umaal umlal umull uqadd16 uqadd8 uqasx uqsax uqsub16 uqsub8 usad8 usada8 usat usat16 uasx usub16 usub8 uxtab uxtab16 uxtah uxtb uxtb16 uxth vabs.f32 vadd.f32 vcmp.f32 vcmpe.f32 vcvt.s32.f32 vcvt.s16.f32 vcvtr.s32.f32 vcvtb.f32.f16 vcvth.f32.f16 vcvttb.f32.f16 vcvttt.f32.f16 vdiv.f32 vfma.f32 vfnma.f32 vfms.f32 vfnms.f32 vldm.f32 vldm.f64 vldr.f32 vldr.f64 vlma.f32 vlms.f32 vmov.f32 vmov vmov vmov vmov vmov vmrs vmsr vmul.f32 vneg.f32 vnmla.f32 vnmls.f32 vnmul vpop vpush vsqrt.f32 vstm vstr.f32 vstr.f64 vsub.f32 vsub.f64 wfe wfi';


		var armregisters = 'r0 r1 r2 r3 r4 r5 r6 r7 r8 r9 r10 r11 r12 r13 sp r14 lr r15 pc';

		this.regexList = [
			{ regex: SyntaxHighlighter.regexLib.doubleQuotedString,		css: 'string' },			// strings
			{ regex: SyntaxHighlighter.regexLib.singleQuotedString,		css: 'string' },			// strings
			{ regex: /#[0-9]*/gm,					css: 'color1 bold' },
			{ regex: /\;.*$/gm,						css: 'comments' },
			{ regex: new RegExp(this.getKeywords(armassembly), 'gm'),	css: 'functions bold' },
			{ regex: new RegExp(this.getKeywords(armregisters), 'gm'),	css: 'keyword bold' }
			];
	};

	Brush.prototype	= new SyntaxHighlighter.Highlighter();
	Brush.aliases	= ['armasm'];

	SyntaxHighlighter.brushes.ArmAsm = Brush;

	// CommonJS
	typeof(exports) != 'undefined' ? exports.Brush = Brush : null;
})();
