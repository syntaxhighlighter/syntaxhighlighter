<?php

/*
 * $Id: SelectorUtils.php 123 2006-09-14 20:19:08Z mrook $
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information please see
 * <http://phing.info>.
 */
 
include_once 'phing/util/StringHelper.php';

/**
 * <p>This is a utility class used by selectors and DirectoryScanner. The
 * functionality more properly belongs just to selectors, but unfortunately
 * DirectoryScanner exposed these as protected methods. Thus we have to
 * support any subclasses of DirectoryScanner that may access these methods.
 * </p>
 * <p>This is a Singleton.</p>
 *
 * @author Hans Lellelid, hans@xmpl.org (Phing)
 * @author Arnout J. Kuiper, ajkuiper@wxs.nl (Ant)
 * @author Magesh Umasankar
 * @author Bruce Atherton, bruce@callenish.com (Ant)
 * @package phing.types.selectors
 */
class SelectorUtils {

    private static $instance;

     /**
      * Retrieves the instance of the Singleton.
      */
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new SelectorUtils();
        }
        return self::$instance;
    }

    /**
     * Tests whether or not a given path matches the start of a given
     * pattern up to the first "**".
     * <p>
     * This is not a general purpose test and should only be used if you
     * can live with false positives. For example, <code>pattern=**\a</code>
     * and <code>str=b</code> will yield <code>true</code>.
     *
     * @param pattern The pattern to match against. Must not be
     *                <code>null</code>.
     * @param str     The path to match, as a String. Must not be
     *                <code>null</code>.
     * @param isCaseSensitive Whether or not matching should be performed
     *                        case sensitively.
     *
     * @return whether or not a given path matches the start of a given
     * pattern up to the first "**".
     */
    public static function matchPatternStart($pattern, $str, $isCaseSensitive = true) {

        // When str starts with a DIRECTORY_SEPARATOR, pattern has to start with a
        // DIRECTORY_SEPARATOR.
        // When pattern starts with a DIRECTORY_SEPARATOR, str has to start with a
        // DIRECTORY_SEPARATOR.
        if (StringHelper::startsWith(DIRECTORY_SEPARATOR, $str) !==
            StringHelper::startsWith(DIRECTORY_SEPARATOR, $pattern)) {
            return false;
        }

        $patDirs = explode(DIRECTORY_SEPARATOR, $pattern);
        $strDirs = explode(DIRECTORY_SEPARATOR, $str);

        $patIdxStart = 0;
        $patIdxEnd   = count($patDirs)-1;
        $strIdxStart = 0;
        $strIdxEnd   = count($strDirs)-1;

        // up to first '**'
        while ($patIdxStart <= $patIdxEnd && $strIdxStart <= $strIdxEnd) {
            $patDir = $patDirs[$patIdxStart];
            if ($patDir == "**") {
                break;
            }
            if (!self::match($patDir, $strDirs[$strIdxStart], $isCaseSensitive)) {
                return false;
            }
            $patIdxStart++;
            $strIdxStart++;
        }

        if ($strIdxStart > $strIdxEnd) {
            // String is exhausted
            return true;
        } elseif ($patIdxStart > $patIdxEnd) {
            // String not exhausted, but pattern is. Failure.
            return false;
        } else {
            // pattern now holds ** while string is not exhausted
            // this will generate false positives but we can live with that.
            return true;
        }
    }
    
    /**
     * Tests whether or not a given path matches a given pattern.
     *
     * @param pattern The pattern to match against. Must not be
     *                <code>null</code>.
     * @param str     The path to match, as a String. Must not be
     *                <code>null</code>.
     * @param isCaseSensitive Whether or not matching should be performed
     *                        case sensitively.
     *
     * @return <code>true</code> if the pattern matches against the string,
     *         or <code>false</code> otherwise.
     */
    public static function matchPath($pattern, $str, $isCaseSensitive = true) {
    
        // When str starts with a DIRECTORY_SEPARATOR, pattern has to start with a
        // DIRECTORY_SEPARATOR.
        // When pattern starts with a DIRECTORY_SEPARATOR, str has to start with a
        // DIRECTORY_SEPARATOR.
        if (StringHelper::startsWith(DIRECTORY_SEPARATOR, $str) !==
            StringHelper::startsWith(DIRECTORY_SEPARATOR, $pattern)) {
            return false;
        }

        $patDirs = explode(DIRECTORY_SEPARATOR, $pattern);
        $strDirs = explode(DIRECTORY_SEPARATOR, $str);

        $patIdxStart = 0;
        $patIdxEnd   = count($patDirs)-1;
        $strIdxStart = 0;
        $strIdxEnd   = count($strDirs)-1;
        
        // up to first '**'
        while ($patIdxStart <= $patIdxEnd && $strIdxStart <= $strIdxEnd) {
            $patDir = $patDirs[$patIdxStart];
            if ($patDir == "**") {
                break;
            }
            if (!self::match($patDir, $strDirs[$strIdxStart], $isCaseSensitive)) {
                return false;
            }
            $patIdxStart++;
            $strIdxStart++;
        }
        if ($strIdxStart > $strIdxEnd) {
            // String is exhausted
            for ($i=$patIdxStart; $i <= $patIdxEnd; $i++) {
                if ($patDirs[$i] != "**") {
                    return false;
                }
            }
            return true;
        } elseif ($patIdxStart > $patIdxEnd) {
            // String not exhausted, but pattern is. Failure.
            return false;
        }

        // up to last '**'
        while ($patIdxStart <= $patIdxEnd && $strIdxStart <= $strIdxEnd) {
            $patDir = $patDirs[$patIdxEnd];
            if ($patDir == "**") {
                break;
            }
            if (!self::match($patDir, $strDirs[$strIdxEnd], $isCaseSensitive)) {
                return false;
            }
            $patIdxEnd--;
            $strIdxEnd--;
        }
        
        if ($strIdxStart > $strIdxEnd) {
            // String is exhausted
            for ($i = $patIdxStart; $i <= $patIdxEnd; $i++) {
                if ($patDirs[$i] != "**") {
                    return false;
                }
            }
            return true;
        }

        while ($patIdxStart != $patIdxEnd && $strIdxStart <= $strIdxEnd) {
            $patIdxTmp = -1;
            for ($i = $patIdxStart+1; $i <= $patIdxEnd; $i++) {
                if ($patDirs[$i] == "**") {
                    $patIdxTmp = $i;
                    break;
                }
            }
            if ($patIdxTmp == $patIdxStart+1) {
                // '**/**' situation, so skip one
                $patIdxStart++;
                continue;
            }
            // Find the pattern between padIdxStart & padIdxTmp in str between
            // strIdxStart & strIdxEnd
            $patLength = ($patIdxTmp-$patIdxStart-1);
            $strLength = ($strIdxEnd-$strIdxStart+1);
            $foundIdx  = -1;

            //strLoop:    (start of outer loop)
            for ($i=0; $i <= $strLength - $patLength; $i++) {                
                for ($j = 0; $j < $patLength; $j++) {
                    $subPat = $patDirs[$patIdxStart+$j+1];
                    $subStr = $strDirs[$strIdxStart+$i+$j];
                    if (!self::match($subPat, $subStr, $isCaseSensitive)) {
                        continue 2; // continue up two levels (to strLoop:)
                    }
                }                                
                $foundIdx = $strIdxStart+$i; // only reached if all sub patterns matched
                break;
            }

            if ($foundIdx == -1) {
                return false;
            }

            $patIdxStart = $patIdxTmp;
            $strIdxStart = $foundIdx + $patLength;
        }

        for ($i = $patIdxStart; $i <= $patIdxEnd; $i++) {
            if ($patDirs[$i] != "**") {
                return false;
            }
        }

        return true;
    }

    /**
     * Tests whether or not a string matches against a pattern.
     * The pattern may contain two special characters:<br>
     * '*' means zero or more characters<br>
     * '?' means one and only one character
     *
     * @param pattern The pattern to match against.
     *                Must not be <code>null</code>.
     * @param str     The string which must be matched against the pattern.
     *                Must not be <code>null</code>.
     * @param isCaseSensitive Whether or not matching should be performed
     *                        case sensitively.
     *
     *
     * @return <code>true</code> if the string matches against the pattern,
     *         or <code>false</code> otherwise.
     */
    public static function match($pattern, $str, $isCaseSensitive = true) {
    
        $patArr = StringHelper::toCharArray($pattern);
        $strArr = StringHelper::toCharArray($str);
        $patIdxStart = 0;
        $patIdxEnd   = count($patArr)-1;
        $strIdxStart = 0;
        $strIdxEnd   = count($strArr)-1;
        
        $containsStar = false;
        for ($i = 0, $size=count($patArr); $i < $size; $i++) {
            if ($patArr[$i] == '*') {
                $containsStar = true;
                break;
            }
        }

        if (!$containsStar) {
            // No '*'s, so we make a shortcut
            if ($patIdxEnd != $strIdxEnd) {
                return false; // Pattern and string do not have the same size
            }
            for ($i = 0; $i <= $patIdxEnd; $i++) {
                $ch = $patArr[$i];
                if ($ch != '?') {
                    if ($isCaseSensitive && $ch !== $strArr[$i]) {
                        return false;// Character mismatch
                    }
                    if (!$isCaseSensitive && strtoupper($ch) !==
                        strtoupper($strArr[$i])) {
                        return false; // Character mismatch
                    }
                }
            }
            return true; // String matches against pattern
        }

        if ($patIdxEnd == 0) {
            return true; // Pattern contains only '*', which matches anything
        }

        // Process characters before first star
        while(($ch = $patArr[$patIdxStart]) != '*' && $strIdxStart <= $strIdxEnd) {
            if ($ch != '?') {
                if ($isCaseSensitive && $ch !== $strArr[$strIdxStart]) {
                    return false;// Character mismatch
                }
                if (!$isCaseSensitive && strtoupper($ch) !==
                    strtoupper($strArr[$strIdxStart])) {
                    return false;// Character mismatch
                }
            }
            $patIdxStart++;
            $strIdxStart++;
        }
        
        if ($strIdxStart > $strIdxEnd) {
            // All characters in the string are used. Check if only '*'s are
            // left in the pattern. If so, we succeeded. Otherwise failure.
            for ($i = $patIdxStart; $i <= $patIdxEnd; $i++) {
                if ($patArr[$i] != '*') {
                    return false;
                }
            }
            return true;
        }

        // Process characters after last star
        while(($ch = $patArr[$patIdxEnd]) != '*' && $strIdxStart <= $strIdxEnd) {
            if ($ch != '?') {
                if ($isCaseSensitive && $ch !== $strArr[$strIdxEnd]) {
                    return false;// Character mismatch
                }
                if (!$isCaseSensitive && strtoupper($ch) !==
                    strtoupper($strArr[$strIdxEnd])) {
                    return false;// Character mismatch
                }
            }
            $patIdxEnd--;
            $strIdxEnd--;
        }
        if ($strIdxStart > $strIdxEnd) {
            // All characters in the string are used. Check if only '*'s are
            // left in the pattern. If so, we succeeded. Otherwise failure.
            for ($i = $patIdxStart; $i <= $patIdxEnd; $i++) {
                if ($patArr[$i] != '*') {
                    return false;
                }
            }
            return true;
        }

        // process pattern between stars. padIdxStart and patIdxEnd point
        // always to a '*'.
        while ($patIdxStart !== $patIdxEnd && $strIdxStart <= $strIdxEnd) {
            $patIdxTmp = -1;
            for ($i = $patIdxStart+1; $i <= $patIdxEnd; $i++) {
                if ($patArr[$i] == '*') {
                    $patIdxTmp = $i;
                    break;
                }
            }
            if ($patIdxTmp === $patIdxStart + 1) {
                // Two stars next to each other, skip the first one.
                $patIdxStart++;
                continue;
            }
            // Find the pattern between padIdxStart & padIdxTmp in str between
            // strIdxStart & strIdxEnd
            $patLength = ($patIdxTmp - $patIdxStart - 1);
            $strLength = ($strIdxEnd - $strIdxStart + 1);
            $foundIdx  = -1;
            
            //strLoop:
            for ($i = 0; $i <= $strLength - $patLength; $i++) {
                for ($j = 0; $j < $patLength; $j++) {
                    $ch = $patArr[$patIdxStart+$j+1];
                    if ($ch != '?') {
                        if ($isCaseSensitive && $ch !== $strArr[$strIdxStart+$i+$j]) {
                               continue 2; //continue to strLoop:
                        }
                        if (!$isCaseSensitive && strtoupper($ch) !==
                            strtoupper($strArr[$strIdxStart+$i+$j])) {
                               continue 2; //continue to strLoop:
                        }
                    }
                }
                // only reached if sub loop completed w/o invoking continue 2
                $foundIdx = $strIdxStart + $i;
                break;
            }

            if ($foundIdx == -1) {
                return false;
            }

            $patIdxStart = $patIdxTmp;
            $strIdxStart = $foundIdx + $patLength;
        }

        // All characters in the string are used. Check if only '*'s are left
        // in the pattern. If so, we succeeded. Otherwise failure.
        for ($i = $patIdxStart; $i <= $patIdxEnd; $i++) {
            if ($patArr[$i] != '*') {
                return false;
            }
        }
        return true;
    }

    /**
     * Returns dependency information on these two files. If src has been
     * modified later than target, it returns true. If target doesn't exist,
     * it likewise returns true. Otherwise, target is newer than src and
     * is not out of date, thus the method returns false. It also returns
     * false if the src file doesn't even exist, since how could the
     * target then be out of date.
     *
     * @param PhingFile $src the original file
     * @param PhingFile $target the file being compared against
     * @param int $granularity the amount in seconds of slack we will give in
     *        determining out of dateness
     * @return whether the target is out of date
     */
    public static function isOutOfDate(PhingFile $src, PhingFile $target, $granularity) {
        if (!$src->exists()) {
            return false;
        }
        if (!$target->exists()) {
            return true;
        }
        if (($src->lastModified() - $granularity) > $target->lastModified()) {
            return true;
        }
        return false;
    }

}

