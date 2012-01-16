<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns:xmi="http://schema.omg.org/spec/XMI/2.1"
                xmlns:uml="http://schema.omg.org/spec/UML/2.1.2"
                xmlns:php="http://schemas/phpdoc/4f0fea3190e75"
                xmlns:dyn="http://exslt.org/dynamic"
                >


  <xsl:output method="text" indent="no" />

  <xsl:param name="docUrl">http://nethgui.nethesis.it/dpdev/Documentation/Api/</xsl:param>
  <xsl:param name="compound" />

  <xsl:template match="/">
    <xsl:variable name="myIndentation" select="'    '" />
    <xsl:variable name="myUrl" select="$docUrl" />

    <xsl:text>digraph Nethgui {&#xA;</xsl:text>
    <xsl:text>    rankdir = "LR"; compound = false; concentrate = true; fontname = "Cantarell"; fontnames="svg"; &#xA;</xsl:text>
    <xsl:text>    node [shape=rect,fontname = "Cantarell"] &#xA;</xsl:text>
    <xsl:text>    edge [arrowhead=vee,style=dashed] &#xA;</xsl:text>

    <xsl:apply-templates select="xmi:XMI/uml:Model/packagedElement[@name='Nethgui']">
      <xsl:with-param name="indentation" select="$myIndentation" />
      <xsl:with-param name="url" select="$myUrl" />
    </xsl:apply-templates>

    <!-- print out classifier relations -->
    <xsl:apply-templates select="//packagedElement[@xmi:type='uml:Interface' or @xmi:type='uml:xxClass']" mode="relation">
        <xsl:with-param name="indentation" select="$myIndentation" />
        <xsl:with-param name="url" select="$myUrl" />
    </xsl:apply-templates>


    <xsl:text>} /* end of file */&#xA;</xsl:text>
  </xsl:template>

  <!-- package subgraph generation -->
  <xsl:template match="packagedElement[@xmi:type='uml:Package']" >
    <xsl:param name="indentation" />
    <xsl:param name="url" />

    <xsl:variable name="myIndentation" select="concat($indentation, '    ')" />
    <xsl:variable name="myUrl" select="concat($url, @name, '/')" />

    <xsl:value-of select="$myIndentation" />subgraph cluster_<xsl:value-of select="@xmi:id" /> {<xsl:text>&#xA;</xsl:text>
    <xsl:value-of select="$myIndentation" /><xsl:text>    ranksep = "1.8 equally"; &#xA;</xsl:text>
    <xsl:value-of select="$myIndentation" /><xsl:text>    shape=component;&#xA;</xsl:text>
    <xsl:value-of select="$myIndentation" /><xsl:text>    color=grey;&#xA;</xsl:text>
    <xsl:value-of select="$myIndentation" /><xsl:text>    URL=&#x22;</xsl:text><xsl:value-of select='concat($myUrl, "package-summary.htm")' /><xsl:text>&#x22;;&#xA;</xsl:text>
    <xsl:value-of select="$myIndentation" /><xsl:text>    </xsl:text>label = "<xsl:value-of select="@name" />";<xsl:text>&#xA;</xsl:text>
 

    <!-- print out classifier declaration -->
    <xsl:apply-templates select="packagedElement[@xmi:type='uml:Interface' or @xmi:type='uml:xxClass']" mode="declaration">
        <xsl:with-param name="indentation" select="$myIndentation" />
        <xsl:with-param name="url" select="$myUrl" />
    </xsl:apply-templates>

    <!-- search for sub-packages -->
    <xsl:apply-templates select="packagedElement[@xmi:type='uml:Package']" >
      <xsl:with-param name="indentation" select="concat($myIndentation, '    ')" />
      <xsl:with-param name="url" select="$myUrl" />
    </xsl:apply-templates>

    <xsl:value-of select="$myIndentation" />} <xsl:text>&#xA;</xsl:text>
  </xsl:template>

  <!-- generate the package classifier index -->
  <xsl:template mode="declaration" match="packagedElement[@xmi:type='uml:Interface' or @xmi:type='uml:Class']">
    <xsl:param name="indentation" />
    <xsl:param name="url">./</xsl:param>
    <xsl:param name="compound" />

    <xsl:variable name="myIndentation" select="concat($indentation, '    ')" />
    <xsl:variable name="myUrl" select="concat($url, 'interface-', @name, '.htm')" />

    <xsl:value-of select="$myIndentation" />"<xsl:value-of select="@xmi:id" />" [label="<xsl:value-of select='@name' />",<xsl:choose>
      <xsl:when test="@xmi:type='uml:Interface'" >color=blue,style=rounded,URL="<xsl:value-of select='$myUrl' />"</xsl:when>
      <xsl:otherwise>color=black</xsl:otherwise></xsl:choose>
      <xsl:text>];&#xA;</xsl:text>
  </xsl:template>

  <!-- generate the classifier relations -->
  <xsl:template mode="relation" match="packagedElement[@xmi:type='uml:Interface' or @xmi:type='uml:Class']" >
    <xsl:param name="indentation" />
    <xsl:param name="url" />

    <xsl:variable name="myUrl" select="$url" />
    <xsl:variable name="myIndentation" select="concat($indentation, '    ')" />
    <!-- "use" dependencies only for interfaces !-->
    <xsl:if test="@xmi:type='uml:Interface' or @xmi:type='uml:xxClass'">
    <xsl:for-each select="ownedOperation">
      <xsl:for-each select="ownedParameter/type">
        <xsl:variable name="refId" select="@xmi:idref"></xsl:variable>
        <xsl:if test="@xmi:idref != ../../../@xmi:id and dyn:evaluate('/descendant::*[@xmi:id=$refId and @xmi:type!=&#x22;uml:DataType&#x22;]')">
          <xsl:value-of select="$myIndentation" />"<xsl:value-of select="../../../@xmi:id"/>" -&gt; "<xsl:value-of select="@xmi:idref"/>" 
          <xsl:if test="$compound">
            <xsl:value-of select="concat('[ltail=&#x22;cluster_', ../../../../@xmi:id, '&#x22;]')" />
          </xsl:if>
          <xsl:text>&#xA;</xsl:text>
        </xsl:if>
      </xsl:for-each>      
    </xsl:for-each>
    </xsl:if>
    <!-- generalizations -->
    <xsl:for-each select="generalization">
      <xsl:value-of select="$myIndentation" />"<xsl:value-of select="../@xmi:id"/>" -&gt; "<xsl:value-of select="@general"/>" [style=solid,arrowhead=empty];<xsl:text>&#xA;</xsl:text>      
    </xsl:for-each>
  </xsl:template>
  

  <xsl:template match="*" />

</xsl:stylesheet>
