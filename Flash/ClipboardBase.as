package
{
	import flash.display.MovieClip;
	import flash.events.MouseEvent;
	import flash.utils.getDefinitionByName;
	import flash.system.System;
	import flash.external.ExternalInterface;
	import flash.events.Event;
	
	public class ClipboardBase extends flash.display.MovieClip
	{
		private var _highlighterId : String;
		
		public function ClipboardBase()
		{
			super();

			addEventListener(MouseEvent.CLICK, onMouseClick);
			
			mouseChildren = false;
			useHandCursor = true;
			buttonMode = true;
			
			var $params : Object = root.loaderInfo.parameters;
			_highlighterId = $params.highlighterId;
		}
		
		private function executeCommand(command : String, message : String = null) : Object
		{
			if (ExternalInterface.available)
				return ExternalInterface.call(
					'SyntaxHighlighter.toolbar.executeCommand',
					null, // sender
					null, // event
					_highlighterId,
					'copyToClipboard',
					{ command : command, message : message }
				);
				
			return null;
		}

		private function onMouseClick(e : MouseEvent) : void
		{
			var $content : Object = executeCommand('get');
			
			if ($content == null)
				return;
				
			try
			{
				System.setClipboard($content as String);
				executeCommand('ok');
			}
			catch(e : Error)
			{
				trace(e.message);
				executeCommand('error', e.message + '\n\n' + $content);
			}
			
		}
		
		private function setState(name : String) : void
		{
			executeCommand('state_' + name);
		}
	}
}