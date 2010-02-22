/*
 * The Apache Software License, Version 1.1
 *
 * Copyright (c) 2002 The Apache Software Foundation.  All rights
 * reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 * 1. Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in
 *    the documentation and/or other materials provided with the
 *    distribution.
 *
 * 3. The end-user documentation included with the redistribution, if
 *    any, must include the following acknowlegement:
 *       "This product includes software developed by the
 *        Apache Software Foundation (http://www.apache.org/)."
 *    Alternately, this acknowlegement may appear in the software itself,
 *    if and wherever such third-party acknowlegements normally appear.
 *
 * 4. The names "Ant" and "Apache Software
 *    Foundation" must not be used to endorse or promote products derived
 *    from this software without prior written permission. For written
 *    permission, please contact apache@apache.org.
 *
 * 5. Products derived from this software may not be called "Apache"
 *    nor may "Apache" appear in their names without prior written
 *    permission of the Apache Group.
 *
 * THIS SOFTWARE IS PROVIDED ``AS IS'' AND ANY EXPRESSED OR IMPLIED
 * WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES
 * OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED.  IN NO EVENT SHALL THE APACHE SOFTWARE FOUNDATION OR
 * ITS CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF
 * USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT
 * OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF
 * SUCH DAMAGE.
 * ====================================================================
 *
 * This software consists of voluntary contributions made by many
 * individuals on behalf of the Apache Software Foundation.  For more
 * information on the Apache Software Foundation, please see
 * <http://www.apache.org/>.
 */

package org.apache.tools.ant.input;

import org.apache.tools.ant.BuildException;

import java.io.FileInputStream;
import java.io.IOException;
import java.util.Properties;

/**
 * Reads input from a property file, the file name is read from the
 * system property ant.input.properties, the prompt is the key for input.
 *
 * @author <a href="mailto:stefan.bodewig@epost.de">Stefan Bodewig</a>
 * @version $Revision: 1.1 $
 * @since Ant 1.5
 */
public class PropertyFileInputHandler implements InputHandler {
    private Properties props = null;

    /**
     * Name of the system property we expect to hold the file name.
     */
    public static final String FILE_NAME_KEY = "ant.input.properties";

    /**
     * Empty no-arg constructor.
     */
    public PropertyFileInputHandler() {
    }

    /**
     * Picks up the input from a property, using the prompt as the
     * name of the property.
     *
     * @exception BuildException if no property of that name can be found.
     */
    public void handleInput(InputRequest request) throws BuildException {
        readProps();
        
        Object o = props.get(request.getPrompt());
        if (o == null) {
            throw new BuildException("Unable to find input for \'"
                                     + request.getPrompt()+"\'");
        }
        request.setInput(o.toString());
        if (!request.isInputValid()) {
            throw new BuildException("Found invalid input " + o
                                     + " for \'" + request.getPrompt() + "\'");
        }
    }

    /**
     * Reads the properties file if it hasn't already been read.
     */
    private synchronized void readProps() throws BuildException {
        if (props == null) {
            String propsFile = System.getProperty(FILE_NAME_KEY);
            if (propsFile == null) {
                throw new BuildException("System property "
                                         + FILE_NAME_KEY
                                         + " for PropertyFileInputHandler not"
                                         + " set");
            }
            
            props = new Properties();
            
            try {
                props.load(new FileInputStream(propsFile));
            } catch (IOException e) {
                throw new BuildException("Couldn't load " + propsFile, e);
            }
        }
    }

}
