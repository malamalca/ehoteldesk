<?xml version="1.0" encoding="UTF-8"?>
<html xsl:version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<body style="font-family:Arial;font-size:12pt;background-color:#EEEEEE">

<xsl:for-each select="knjigaGostov/row">
<table width="100%" style="font-size: 10px; border: solid 1px black;">
    <tr>
        <td>Obrat:</td><td><xsl:value-of select="@idNO"/></td>
    </tr>
    <tr>
        <td>Gost:</td><td><xsl:value-of select="@zst"/>.<xsl:value-of select="@ime"/>.<xsl:value-of select="@pri"/>.<xsl:value-of select="@sp"/>.<xsl:value-of select="@dtRoj"/>.<xsl:value-of select="@drzava"/></td>
    </tr>
    <tr>
        <td>Ident.:</td><td><xsl:value-of select="@vrstaDok"/>-<xsl:value-of select="@idStDok"/></td>
    </tr>
    <tr>
        <td>Od-do:</td><td><xsl:value-of select="@casPrihoda"/>...<xsl:value-of select="@casOdhoda"/></td>
    </tr>
    <tr>
        <td>Obračun:</td><td><xsl:value-of select="@status"/>.<xsl:value-of select="@ttObracun"/>.<xsl:value-of select="@ttVisina"/></td>
    </tr>
</table>
</xsl:for-each>

</body>
</html>
