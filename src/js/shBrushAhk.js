/**
 * SyntaxHighlighter
 * http://alexgorbatchev.com/
 *
 * This brush was originally provided by "mjneish", created by user "n-l-i-d"
 * homepage:   http://users.on.net/~mjneish
 * brush page: http://www.autohotkey.com/forum/topic46947.html
 * test page:  http://users.on.net/~mjneish/syntax/test.html
 *
 * Fixed, Enhanced & Updated by Avi Aryan
 * homepage:   http://aviaryan.github.io
 * git repo:   http://github.com/aviaryan/highlighter-ahk-zenburn
 *
 * Official Themes
 * Zenburn-A , GitHub
 */


SyntaxHighlighter.brushes.Ahk = function()
{
var commands = 'AutoTrim BlockInput Break Click ClipWait Continue Control ControlClick ControlFocus ControlGet' +
' ControlGetFocus ControlGetPos ControlGetText ControlMove ControlSend ControlSendRaw ControlSetText CoordMode Critical DetectHiddenText' +
' DetectHiddenWindows Drive DriveGet DriveSpaceFree Edit Else EnvAdd EnvDiv EnvGet EnvMult' +
' EnvSet EnvSub EnvUpdate Exit ExitApp FileAppend FileCopy FileCopyDir FileCreateDir FileCreateShortcut' +
' FileDelete FileGetAttrib FileGetShortcut FileGetSize FileGetTime FileGetVersion FileInstall FileMove FileMoveDir FileRead' +
' FileReadLine FileRecycle FileRecycleEmpty FileRemoveDir FileSelectFile FileSelectFolder FileSetAttrib FileSetTime FormatTime GetKeyState' +
' Gosub Goto GroupActivate GroupAdd GroupClose GroupDeactivate Gui GuiControl GuiControlGet Hotkey' +
' If IfEqual IfExist IfGreater IfGreaterOrEqual IfInString IfLess IfLessOrEqual IfMsgBox IfNotEqual' +
' IfNotExist IfNotInString IfWinActive IfWinExist IfWinNotActive IfWinNotExist ImageSearch IniDelete IniRead IniWrite' +
' Input InputBox KeyHistory KeyWait ListHotkeys ListLines ListVars Loop Menu MouseClick' +
' MouseClickDrag MouseGetPos MouseMove MsgBox OnExit OutputDebug Pause PixelGetColor PixelSearch PostMessage' +
' Process Progress Random RegDelete RegRead RegWrite Reload Repeat Return Run' +
' RunAs RunWait Send SendEvent SendInput SendMessage SendMode SendPlay SendRaw SetBatchLines' +
' SetCapslockState SetControlDelay SetDefaultMouseSpeed SetEnv SetFormat SetKeyDelay SetMouseDelay SetNumlockState SetScrollLockState SetStoreCapslockMode' +
' SetTimer SetTitleMatchMode SetWinDelay SetWorkingDir Shutdown Sleep Sort SoundBeep SoundGet SoundGetWaveVolume' +
' SoundPlay SoundSet SoundSetWaveVolume SplashImage SplashTextOff SplashTextOn SplitPath StatusBarGetText StatusBarWait StringCaseSense' +
' StringGetPos StringLeft StringLen StringLower StringMid StringReplace StringRight StringSplit StringTrimLeft StringTrimRight' +
' StringUpper Suspend SysGet Thread ToolTip Transform TrayTip URLDownloadToFile While WinActivate' +
' WinActivateBottom WinClose WinGet WinGetActiveStats WinGetActiveTitle WinGetClass WinGetPos WinGetText WinGetTitle WinHide' +
' WinKill WinMaximize WinMenuSelectItem WinMinimize WinMinimizeAll WinMinimizeAllUndo WinMove WinRestore WinSet WinSetTitle' +
' WinShow WinWait WinWaitActive WinWaitClose WinWaitNotActive';

var keys = 'Shift LShift RShift Alt' +
' LAlt RAlt Control LControl RControl Ctrl LCtrl RCtrl LWin RWin' +
' AppsKey AltDown AltUp ShiftDown ShiftUp CtrlDown CtrlUp LWinDown LWinUp RWinDown' +
' RWinUp LButton RButton MButton WheelUp WheelDown WheelLeft WheelRight XButton1 XButton2' +
' Joy1 Joy2 Joy3 Joy4 Joy5 Joy6 Joy7 Joy8 Joy9 Joy10' +
' Joy11 Joy12 Joy13 Joy14 Joy15 Joy16 Joy17 Joy18 Joy19 Joy20' +
' Joy21 Joy22 Joy23 Joy24 Joy25 Joy26 Joy27 Joy28 Joy29 Joy30' +
' Joy31 Joy32 JoyX JoyY JoyZ JoyR JoyU JoyV JoyPOV JoyName' +
' JoyButtons JoyAxes JoyInfo Space Tab Enter Escape Esc BackSpace BS' +
' Delete Del Insert Ins PGUP PGDN Home End Up Down' +
' Left Right PrintScreen CtrlBreak Pause ScrollLock CapsLock NumLock Numpad0 Numpad1' +
' Numpad2 Numpad3 Numpad4 Numpad5 Numpad6 Numpad7 Numpad8 Numpad9 NumpadMult NumpadAdd' +
' NumpadSub NumpadDiv NumpadDot NumpadDel NumpadIns NumpadClear NumpadUp NumpadDown NumpadLeft NumpadRight' +
' NumpadHome NumpadEnd NumpadPgup NumpadPgdn NumpadEnter F1 F2 F3 F4 F5' +
' F6 F7 F8 F9 F10 F11 F12 F13 F14 F15' +
' F16 F17 F18 F19 F20 F21 F22 F23 F24 Browser_Back' +
' Browser_Forward Browser_Refresh Browser_Stop Browser_Search Browser_Favorites Browser_Home Volume_Mute Volume_Down Volume_Up Media_Next' +
' Media_Prev Media_Stop Media_Play_Pause Launch_Mail Launch_Media Launch_App1 Launch_App2';

var funcs = '¶Dummyfunction_for_highlight Abs ACos Asc ASin ATan Ceil Chr Cos DllCall Exp' +
' FileExist Floor GetKeyState IL_Add IL_Create IL_Destroy InStr IsFunc IsLabel Ln' +
' Log LV_Add LV_Delete LV_DeleteCol LV_GetCount LV_GetNext LV_GetText LV_Insert LV_InsertCol LV_Modify' +
' LV_ModifyCol LV_SetImageList Mod NumGet NumPut OnMessage RegExMatch RegExReplace RegisterCallback Round' +
' SB_SetIcon SB_SetParts SB_SetText Sin Sqrt StrGet StrLen SubStr Tan TV_Add TV_Delete' +
' TV_GetChild TV_GetCount TV_GetNext TV_Get TV_GetParent TV_GetPrev TV_GetSelection TV_GetText TV_Modify VarSetCapacity' +
' WinActive WinExist';

var keywords = 'Pixel Mouse Screen Relative RGB LTrim RTrim Join Low BelowNormal' +
' Normal AboveNormal High Realtime ahk_id ahk_pid ahk_class ahk_group Between Contains' +
' In Is Integer Float IntegerFast FloatFast Number Digit Xdigit Alpha' +
' Upper Lower Alnum Time Date Not Or And AlwaysOnTop Topmost' +
' Top Bottom Transparent TransColor Redraw Region ID IDLast ProcessName MinMax' +
' ControlList Count List Capacity StatusCD Eject Lock Unlock Label FileSystem' +
' Label SetLabel Serial Type Status static global local ByRef Seconds' +
' Minutes Hours Days Read Parse Logoff Close Error Single Tray' +
' Add Rename Check UnCheck ToggleCheck Enable Disable ToggleEnable Default NoDefault' +
' Standard NoStandard Color Delete DeleteAll Icon NoIcon Tip Click Show' +
' MainWindow NoMainWindow UseErrorLevel Text Picture Pic GroupBox Button Checkbox Radio' +
' DropDownList DDL ComboBox ListBox ListView DateTime MonthCal Slider StatusBar Tab' +
' Tab2 TreeView UpDown IconSmall Tile Report SortDesc NoSort NoSortHdr Grid' +
' Hdr AutoSize Range xm ym ys xs xp yp Font' +
' Resize Owner Submit NoHide Minimize Maximize Restore NoActivate NA Cancel' +
' Destroy Center Margin MaxSize MinSize OwnDialogs GuiEscape GuiClose GuiSize GuiContextMenu' +
' GuiDropFiles TabStop Section AltSubmit Wrap HScroll VScroll Border Top Bottom' +
' Buttons Expand First ImageList Lines WantCtrlA WantF2 Vis VisFirst Number' +
' Uppercase Lowercase Limit Password Multi WantReturn Group Background bold italic' +
' strike underline norm BackgroundTrans Theme Caption Delimiter MinimizeBox MaximizeBox SysMenu' +
' ToolWindow Flash Style ExStyle Check3 Checked CheckedGray ReadOnly Password Hidden' +
' Left Right Center NoTab Section Move Focus Hide Choose ChooseString' +
' Text Pos Enabled Disabled Visible LastFound LastFoundExist AltTab ShiftAltTab AltTabMenu' +
' AltTabAndMenu AltTabMenuDismiss NoTimers Interrupt Priority WaitClose Wait Exist Close Blind' +
' Unicode Asc Chr Deref Mod Pow Exp Sqrt' +
' Log Ln Round Ceil Floor Abs Sin Cos Tan ASin' +
' ACos ATan BitNot BitAnd BitOr BitXOr BitShiftLeft BitShiftRight Yes No' +
' Ok Cancel Abort Retry Ignore TryAgain On Off All HKEY_LOCAL_MACHINE' +
' HKEY_USERS HKEY_CURRENT_USER HKEY_CLASSES_ROOT HKEY_CURRENT_CONFIG HKLM HKU HKCU HKCR HKCC REG_SZ' +
' REG_EXPAND_SZ REG_MULTI_SZ REG_DWORD REG_BINARY';

var variables = 'A_AhkPath A_AhkVersion A_AppData A_AppDataCommon A_AutoTrim A_BatchLines A_CaretX A_CaretY A_ComputerName ' +
'A_ControlDelay A_Cursor A_DD A_DDD A_DDDD A_DefaultMouseSpeed A_Desktop A_DesktopCommon A_DetectHiddenText A_DetectHiddenWindows ' +
'A_EndChar A_EventInfo A_ExitReason A_FormatFloat A_FormatInteger A_Gui A_GuiEvent A_GuiControl A_GuiControlEvent A_GuiHeight ' +
'A_GuiWidth A_GuiX A_GuiY A_Hour A_IconFile A_IconHidden A_IconNumber A_IconTip A_Index A_IPAddress1 ' +
'A_IPAddress2 A_IPAddress3 A_IPAddress4 A_ISAdmin A_IsCompiled A_IsSuspended A_KeyDelay A_Language A_LastError A_LineFile ' +
'A_LineNumber A_LoopField A_LoopFileAttrib A_LoopFileDir A_LoopFileExt A_LoopFileFullPath A_LoopFileLongPath A_LoopFileName A_LoopFileShortName A_LoopFileShortPath ' +
'A_LoopFileSize A_LoopFileSizeKB A_LoopFileSizeMB A_LoopFileTimeAccessed A_LoopFileTimeCreated A_LoopFileTimeModified A_LoopReadLine A_LoopRegKey A_LoopRegName A_LoopRegSubkey ' +
'A_LoopRegTimeModified A_LoopRegType A_MDAY A_Min A_MM A_MMM A_MMMM A_Mon A_MouseDelay A_MSec ' +
'A_MyDocuments A_Now A_NowUTC A_NumBatchLines A_OSType A_OSVersion A_PriorHotkey A_ProgramFiles A_Programs A_ProgramsCommon ' +
'A_ScreenHeight A_ScreenWidth A_ScriptDir A_ScriptFullPath A_ScriptName A_Sec A_Space A_StartMenu A_StartMenuCommon A_Startup ' +
'A_StartupCommon A_StringCaseSense A_Tab A_Temp A_ThisHotkey A_ThisMenu A_ThisMenuItem A_ThisMenuItemPos A_TickCount A_TimeIdle ' +
'A_TimeIdlePhysical A_TimeSincePriorHotkey A_TimeSinceThisHotkey A_TitleMatchMode A_TitleMatchModeSpeed A_UserName A_WDay A_WinDelay A_WinDir A_WorkingDir ' +
'A_YDay A_YEAR A_YWeek A_YYYY Clipboard ClipboardAll ComSpec ErrorLevel ProgramFiles True False';

var directives = 'AllowSameLineComments ClipboardTimeout CommentFlag ErrorStdOut EscapeChar HotkeyInterval HotkeyModifierTimeout Hotstring IfWinActive IfWinExist' +
' IfWinNotActive IfWinNotExist Include IncludeAgain InstallKeybdHook InstallMouseHook KeyHistory LTrim MaxHotkeysPerInterval MaxMem' +
' MaxThreads MaxThreadsBuffer MaxThreadsPerHotkey NoEnv NoTrayIcon Persistent SingleInstance UseHook WinActivateForce';

// css color keywords:
// The regex items are processed in alphabetical order of regex term

this.regexList = [
{ regex: /\b[0-9]+(\.)?[0-9]*/gmi, css: 'color2' },
{ regex: /[\+\*\-\=\?>:\\\/<\&%]/gm, css: 'preprocessor' }, //operators
{ regex: /[^\(\); \t,\n\+\*\-\=\?>:\\\/<\&%]+?(?=\(.*?\))/gmi, css: 'functions'}, //UD Functions CALL
{ regex: /^[ \t]*[\S]+?(?=\(.*\))/gmi, css: 'functions'}, //UD Functions (using Look-ahead ass)
// UD FUNCTIONS = COLOR3 in css
{ regex: /;.*$/gm, css: 'comments' }, // one line comments
{ regex: SyntaxHighlighter.regexLib.multiLineCComments, css: 'comments' }, // multiline comments
{ regex: /"(([^"]|"")*)"/gmi, css: 'string' }, // double quoted strings
{ regex: /\%\w+\%/g, css: 'variable' }, // variables

// a bug in SyntaxHighlighter in which &>< are replaced by &amp; *gt; &lt;
// as gt; and lt; have 3 chars , three dots are used . </> is replaced by &gt;/&lt; from where & is replaced back to </>
{ regex: /[&<>].../gm, css: 'plain' },
// replaces &amp; back to & , 3 dots are as [^something]... consumes 4 characters viz. amp;
{ regex: /[&][^gl].../gm, css: 'plain'},

{ regex: new RegExp(this.getKeywords(variables), 'gmi'), css: 'variable' }, // ahk variables
{ regex: new RegExp(this.getKeywords(funcs), 'gmi'), css: 'functions' }, // functions
{ regex: new RegExp(this.getKeywords(keys), 'gmi'), css: 'value' }, // values (actually Keys)
{ regex: new RegExp(this.getKeywords(commands), 'gmi'), css: 'constants' }, // constants
{ regex: new RegExp(this.getKeywords(directives), 'gmi'), css: 'keyword' }, // directives
{ regex: /^[ \t]*[\S]+?(?=:)/gmi, css: 'color1' }, // labels
{ regex: new RegExp(this.getKeywords(keywords), 'gmi'), css: 'keyword' } // keywords
];

var r = SyntaxHighlighter.regexLib;
this.forHtmlScript(r.scriptScriptTags);

};

SyntaxHighlighter.brushes.Ahk.prototype = new SyntaxHighlighter.Highlighter();
SyntaxHighlighter.brushes.Ahk.aliases = ['ahk', 'autohotkey']; 
typeof(exports) != 'undefined' ? exports.SyntaxHighlighter.brushes.Ahk = SyntaxHighlighter.brushes.Ahk : null;
